<?php
/**
 *
 * Abstract server adapter.
 *
 * Heavily modified and refactored from Zend_Server and related classes.
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

/**
 *
 * Abstract server adapter.
 *
 * @category Tipos
 *
 * @package Sungrazr_Controller_Server_Adapter
 *
 */
abstract class Sungrazr_Controller_Server_Adapter
    extends Solar_Base implements Serializable {

    /**
     *
     * User-provided configuration.
     *
     * Config keys are ...
     *
     * `return`
     * : (bool) Whether or not to return a response. Defaults to **FALSE**.
     * When **TRUE**, the fetch() method will not send output but will
     * instead return the response that would otherwise have been sent.
     *
     * `headers`
     * : (array) Associative array of headers to return with every response.
     *
     * `content_types`
     * : (array) List of acceptable content types for requests and responses.
     * The Content-Type of the inbound request is checked against the
     * 'request' list prior to unserializing the inbound request. The Accept
     * header of the request is examined for preferred types of supported
     * responses in the serialization of the response. If Accept header
     * is omitted or indicates a wildcard, the first supported response
     * type is used.
     *
     * `server_request`
     * : (array) Parameters for the request object dependency used within the
     * Adapter This bypasses the Solar_Request dependency that Solar sets up
     * by default to allow for extended request objects easily. The first
     * element of the array is the class name to pass to [[Solar::dependency]],
     * and the second is the second parameter to pass to Solar::dependency.
     *
     * `send_length`
     * : (bool) Send the content-length of the response. If the web server
     * is performing automatic compression after the output of the PHP script,
     * you may wish to turn this off so the connection doesn't hang due to
     * a mismatch between the advertised size of the Content-Length and the
     * actual size of the compressed response.
     *
     * @var array
     *
     */
    protected $_Sungrazr_Controller_Server_Adapter = array(
        'classes'           => null,
        'return'            => false,
        'headers'           => array(),
        'content_types'     => array(
            'request'       => array('text/xml'),
            'response'      => array('text/xml'),
        ),
        'server_request'    => array(
            'class'         => 'Solar_Request',
            'spec'          => null,
        ),
        'send_length'       => true,
        'api'               => null,
    );

    /**
     *
     * Sungrazr_Controller_Server_Api object handle.
     *
     * @var Sungrazr_Controller_Server_Api
     *
     */
    protected $_api;

    /**
     *
     * HTTP headers to send with the response.
     *
     * @var array
     *
     */
    protected $_headers = array();

    /**
     *
     * Solar_Request-based dependency.
     *
     * @var Solar_Request
     *
     */
    protected $_request;

    /**
     *
     * Array of introspective API information, indexed by callable method
     * names instead of internal class names.
     *
     * @var array
     *
     */
    protected $_server_api = array();

    /**
     *
     * Special introspective methods provided by the server.
     *
     * @var array
     *
     */
    protected $_server_specials = array();

    /**
     *
     * Constructor.
     *
     * @param array $config User-provided configuration values.
     *
     */
    public function __construct($config = null)
    {
        // basic construction
        parent::__construct($config);

        // pick up config options
        $this->_headers = array_merge($this->_headers,
            $this->_config['headers']);

        // set up request dependency
        if (! Solar_Registry::exists('server_request')) {
            Solar_Registry::set(
                'server_request',
                $this->_config['server_request']['class'],
                $this->_config['server_request']['spec']
            );
        }
        $this->_request = Solar_Registry::get('server_request');

        // make extra-sure that no headers are output prematurely
        ob_start();

        // API object to perform introspection and validation of requests.
        $this->_api = Solar::dependency('Sungrazr_Controller_Server_Api',
            $this->_config['api']);

        // define special methods
        $this->_server_specials = array(
            'system.listMethods'        => array(&$this, 'listMethods'),
            'system.describe'           => array(&$this, 'listMethods'),
            'system.methodSignature'    => array(&$this, 'methodSignature'),
            'system.methodHelp'         => array(&$this, 'methodHelp'),
        );
    }

    /**
     *
     * Attach a class to a server.
     *
     * @param mixed $class The class to add to the server API.
     *
     * @param string $namespace Optionally specify a namespace for the methods
     * of the added class. If empty, no namespace will be used. Default is
     * 'auto', which automatically sets the namespace as the lowercased final
     * segment of the class name.
     *
     */
    public function addClass($class, $namespace = 'auto')
    {
        $this->_api->addClass($class, $namespace);
        $this->_server_api = array_merge(
            $this->_server_api,
            $this->_api->api[$class]['server_api']
        );
    }

    /**
     *
     * Generate a server fault.
     *
     * @param mixed $fault A description of the fault. Will be serialized by
     * the server adapter before it is returned.
     *
     * @param int $code Fault code
     *
     * @return mixed
     *
     */
    abstract public function fault($fault = null, $code = 404);

    /**
     *
     * Return the internal API information.
     *
     * @return Sungrazr_Controller_Server_Api
     *
     */
    public function getApi()
    {
        return $this->_api;
    }

    /**
     *
     * Handle a request.
     *
     * Dispatches server-side call to appropriate method and returns a
     * response.
     *
     * @return mixed
     *
     */
    abstract public function fetch($spec = null);

    /**
     *
     * Return a list of methods the server supports, by name.
     *
     * @see http://scripts.incutio.com/xmlrpc/introspection.html
     *
     * @see http://xmlrpc-c.sourceforge.net/introspection.html
     *
     * @return array
     *
     */
    public function listMethods()
    {
        $methods = array_keys($this->_server_api);
        foreach ($this->_server_specials as $method => $callable) {
            $methods[] = $method;
        }
        sort($methods);
        return $methods;
    }

    /**
     *
     * Return a description of the argument format a particular method
     * expects.
     *
     * The result is an array of strings. The first element tells the type
     * of the method's result. The rest (if any) tell the types of the
     * method's parameters, in order.
     *
     * @see http://scripts.incutio.com/xmlrpc/introspection.html
     *
     * @see http://xmlrpc-c.sourceforge.net/introspection.html
     *
     * @return array
     *
     */
    public function methodSignature($method = null)
    {

    }

    /**
     *
     * Returns a text description of a particular method.
     *
     * The returned string is intended for human use. The server may give as
     * much or as little detail as it wants, including an empty string.
     *
     * The string may contain HTML.
     *
     * @see http://scripts.incutio.com/xmlrpc/introspection.html
     *
     * @see http://xmlrpc-c.sourceforge.net/introspection.html
     *
     * @return string
     *
     */
    public function methodHelp($method = null)
    {

    }

    /**
     *
     * Serializable: Returns a string representation of the instance.
     *
     * @return string
     *
     */
    public function serialize()
    {
        return $this->_serialize();
    }

    /**
     *
     * Serializable: Populates the instance with serialized data.
     *
     * @return bool
     *
     */
    public function unserialize($serialized)
    {
        return $this->_unserialize($serialized);
    }

    /**
     *
     * Common response method leveraging Serializable interface.
     *
     * @return string
     *
     */
    protected function _response()
    {
        $response = $this->serialize();

        if (! $this->_config['return']) {

            // merge headers
            $this->_headers = array_merge(
                $this->_config['headers'],
                $this->_headers
            );

            // set output type appropriately
            if (empty($this->_headers['Content-Type'])) {
                // start with default
                $content_type = $this->_config['content_types']['response'][0];

                // now check if caller would prefer something specific that
                // is supported
                $accept = $this->_request->http('Accept', false);
                if ($accept && strpos($accept, '*/*') === false) {
                    // Accept header contains no wildcard
                    foreach ($this->_config['content_types']['response'] as $rt) {
                        if (strpos($accept, $rt) !== false) {
                            $content_type = $rt;
                            break;
                        }
                    }
                }

                // set the type
                $this->_headers['Content-Type'] = $content_type;
            }

            // send content length?
            if ($this->_config['send_length']) {
                $len = strlen($response);
                // any buffered output yet?
                $buf = ob_get_contents();
                if ($buf !== false) {
                    $buflen = strlen($buf);
                    if ($buflen != $len) {
                        $len = $len + $buflen;
                    }
                }
                $this->_headers['Content-Length'] = $len;
            }

            // append to Server header
            $server_class = get_class($this);
            if (! empty($this->_headers['Server'])) {
                $this->_headers['Server'] = rtrim($this->_headers['Server']) .
                    ' (' . $server_class . ')';
            } else {
                $this->_headers['Server'] = $server_class;
            }

            if (! empty($this->_headers) && ! headers_sent()) {
                foreach ($this->_headers as $header => $value) {
                    $h = trim($header);
                    $v = trim($value);
                    if (! empty($v)) {
                        $h .= ': ' . $v;
                    }
                    header($h);
                }
            }
            echo $response;
            return;
        }
        return $response;
    }

    /**
     *
     * serialize() Implementation details for the Serializable interface.
     *
     * @return string
     *
     */
    abstract protected function _serialize();

    /**
     *
     * unserialize() Implementation details for the Serializable interface.
     *
     * @param mixed $serialized Value to unserialize.
     *
     * @return bool
     *
     */
    abstract protected function _unserialize($serialized);
}