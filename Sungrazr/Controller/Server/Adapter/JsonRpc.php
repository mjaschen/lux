<?php
/**
 *
 * JSON-RPC 1.0 server adapter.
 *
 * Developed for, and then donated by, Mashery.com <http://mashery.com>.
 *
 * @category Tipos
 *
 * @package Sungrazr_Controller_Server_Adapter
 *
 * @author Clay Loveless <clay@killersoft.com>
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 *
 * @version SVN: $Id$
 *
 */

class Sungrazr_Controller_Server_Adapter_JsonRpc extends
    Sungrazr_Controller_Server_Adapter {

    /**
     *
     * User-provided configuration.
     *
     * Config keys are ...
     *
     * `content_types`
     * : (array) List of acceptable content types for inbound requests.
     *
     * @var array
     *
     */
    protected $_Sungrazr_Controller_Server_Adapter_JsonRpc = array(
        'return'        => false,
        'headers'       => array(),
        'content_types' => array(
            'request'   => array(
                'text/javascript',
                'application/json'
            ),
            'response'  => array(
                'text/javascript',
                'text/x-javascript',
                'application/javascript',
                'application/x-javascript',
            ),
        ),
        'server_request'    => array(
            'class'         => 'Solar_Request',
            'spec'          => null,
        ),
    );

    /**
     *
     * From the request: method being called
     *
     * @param string
     *
     */
    public $method = null;

    /**
     *
     * From the request: unnamed parameters to pass to the method.
     *
     * @param array
     *
     */
    public $params = null;

    /**
     *
     * For the response: mixed result of the method call.
     *
     * @param mixed
     *
     */
    public $result = null;

    /**
     *
     * For the response: mixed error message. If no error occurred, this
     * should be null. Otherwise, it should be an object.
     *
     * @param mixed
     *
     */
    public $error = null;

    /**
     *
     * For request and response: if request was tagged with an id, it is
     * stored here to be attached to the response.
     *
     * If request was not tagged with an id, JSON-RPC 1.0 indicates that
     * id value should be null.
     *
     * @param mixed
     *
     */
    public $id = null;

    /**
     *
     * Handle a JSON-RPC request.
     *
     * Rudimentary request validation is performed. Handler methods should
     * perform in-depth validation on passed parameters.
     *
     * @param Solar_Request $request A Solar Request object. Raw POST data
     * will be read from php://input.
     *
     * @return mixed
     *
     */
    public function fetch($spec = null)
    {
        // avoid bad/inappropriate requests if possible
        if (! $this->_validate()) {
            return;
        }

        // call the handler object + method
        $callback = $this->_server_api[$this->method]['callback'];
        $obj = $this->_config['caller'];

        // should we return a fault immediately?
        // Construction/setup could have failed.
        if ($obj->isFault === true) {
            $fault = $obj->getFault();
            return $this->fault(
                $fault['message'],
                $fault['code']
            );
        }

        // No fault during setup, so call the method
        $this->result = call_user_func_array(
            array($obj, $callback[1]),
            $this->params
        );

        // should we return a fault now?
        if ($obj->isFault === true) {
            $fault = $obj->getFault();
            return $this->fault(
                $fault['message'],
                $fault['code']
            );
        }

        // done!
        return $this->_response();
    }

    /**
     *
     * Generate a server fault.
     *
     * @param mixed $fault
     *
     * @param int $code
     *
     * @return mixed
     *
     */
    public function fault($fault = null, $code = 404)
    {
        // result MUST be null if a fault occurs.
        $this->result = null;

        // Construct an error object
        $err = new stdClass;
        $err->name = 'JSONRPCError';
        $err->code = (int) $code;
        $err->message = $fault;
        $this->error = $err;
        return $this->_response();
        exit((int) $code);
    }

    /**
     *
     * Handle special method calls.
     *
     * Returns the response from the server special method if appropriate.
     * Otherwise, returns **TRUE**.
     *
     * @return mixed
     *
     */
    protected function _handleSpecials()
    {
        if (array_key_exists($this->method, $this->_server_specials)) {
            $this->result = call_user_func_array(
                $this->_server_specials[$this->method],
                $this->params
            );
            return $this->_response();
        }
        return true;
    }

    /**
     *
     * Read raw post data and unserialize it.
     *
     * @param Solar_Request $request Solar_Request object
     *
     * @return bool
     *
     */
    protected function _loadInput()
    {
        $input = file_get_contents('php://input');
        if (empty($input)) {
            return $this->fault(
                'Unable to read from input',
                101
            );
        }

        // pick out content length from raw input
        $len = $this->_request->http('Content-Length', false);
        if (! $len) {
            return $this->fault(
                'No Content-Length information found in request.',
                102
            );
        }

        // use Content-Length header to determine how much to retrieve
        $body = substr($input, 0, $len);

        // attempt to unserialize
        return $this->unserialize($body);
    }

    /**
     *
     * Reject calls to unknown methods.
     *
     * Returns a server fault for unknown methods, **TRUE** if method is
     * known.
     *
     * @return mixed
     *
     */
    protected function _rejectUnknownMethods()
    {
        if (empty($this->_server_api[$this->method])) {
            return $this->fault(
                'Unknown method: ' . $this->method,
                103
            );
        }
        return true;
    }

    /**
     *
     * Serialize the data in this object to a JSON-RPC response.
     *
     * @return string
     *
     */
    protected function _serialize()
    {
        return json_encode(array(
            'result' => $this->result,
            'error' => $this->error,
            'id' => $this->id
        ));
    }

    /**
     *
     * Populate this request with data specified in the serialized request.
     *
     * @return bool
     *
     */
    protected function _unserialize($serialized)
    {
        // JSON-RPC 1.0 properties
        $properties = array('method', 'params', 'id');
        $obj = json_decode($serialized);

        if (! is_object($obj)) {
            return $this->fault(
                'The request was not a valid JSON object; It could not be parsed.',
                -1
            );
        }

        foreach ($properties as $p) {
            if (! empty($obj->{$p})) {
                $this->{$p} = $obj->{$p};
            }
        }
        return true;
    }

    /**
     *
     * Wrapper for all validation methods.
     *
     * @return bool
     *
     */
    protected function _validate()
    {
        // JSON-RPC over HTTP must be a POST
        $ok = $this->_validateRequestMethod();
        if ($ok !== true) return false;

        // Confirm that the content-type is acceptable
        $ok = $this->_validateRequestContentType();
        if ($ok !== true) return false;

        // attempt to load the request
        $ok = $this->_loadInput();
        if ($ok !== true) return false;

        // special request?
        $ok = $this->_handleSpecials();
        if ($ok !== true) return false;

        // is this an unknown method?
        $ok = $this->_rejectUnknownMethods();
        if ($ok !== true) return false;

        // validate type
        $ok = $this->_validateParameterType();
        if ($ok !== true) return false;

        // validate parameter count
        $ok = $this->_validateParameterCount();
        if ($ok !== true) return false;

        return true;
    }

    /**
     *
     * Validate the parameter count against what the handler method is
     * expecting.
     *
     * Returns a server fault on failure, **TRUE** on success.
     *
     * @return mixed
     *
     */
    protected function _validateParameterCount()
    {
        $expected_num = count($this->_server_api[$this->method]['params']);
        $actual_num   = count($this->params);

        if ($actual_num != $expected_num) {
           return $this->fault(
                "Invalid call to {$this->method}. " .
                    "Requires {$expected_num} parameters, " .
                    "{$actual_num} parameters received",
                400
            );
        }
        return true;
    }

    /**
     *
     * Make sure the params value is an array.
     *
     * Returns a server fault on failure, **TRUE** on success.
     *
     * @return mixed
     *
     */
    protected function _validateParameterType()
    {
        // params should be an array
        if (! is_array($this->params)) {
            return $this->fault(
                "JSON-RPC expects \"params\" to be an array of objects.",
                400
            );
        }

        // each element of params should be an object
        foreach ($this->params as $param) {
            if (! is_object($param)) {
                return $this->fault(
                    "JSON-RPC expects \"params\" to be an array of objects.",
                    400
                );
            }
        }

        return true;
    }

    /**
     *
     * Make sure the request content type is acceptable.
     *
     * Returns a server fault on failure, **TRUE** on success.
     *
     * @return mixed
     *
     */
    protected function _validateRequestContentType()
    {
        return true;
        $ct = strtolower($this->_request->server('CONTENT_TYPE', 'unknown'));
        if (! in_array($ct, $this->_config['content_types']['request'])) {
            return $this->fault(
                "JSON-RPC does not support Content-Type {$ct} requests.",
                400
            );
        }
        return true;
    }

    /**
     *
     * Make sure the request is a POST.
     *
     * Returns a server fault on failure, **TRUE** on success.
     *
     * @return mixed
     *
     */
    protected function _validateRequestMethod()
    {
        // JSON-RPC over HTTP must be a POST
        if (! $this->_request->isPost()) {
            return $this->fault(
                'JSON-RPC calls over HTTP must be made using POST requests',
                100
            );
        }
        return true;
    }
}