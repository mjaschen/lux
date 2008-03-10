<?php
/**
 *
 * The equivalent of a page-controller to handle service requests.
 *
 * Provides a base for building external API wrapper classes.
 *
 * Developed for, and then donated by, Mashery.com <http://mashery.com>.
 *
 * @category Tipos
 *
 * @package Sungrazr_Controller_Server
 *
 * @author Clay Loveless <clay@killersoft.com>
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 *
 * @version SVN: $Id$
 *
 */

abstract class Sungrazr_Controller_Service extends Solar_Base {

    /**
     *
     * User-provided configuration.
     *
     * Config keys are ...
     *
     * `server_request`
     * : (dependency) Server's Solar_Request dependency
     *
     * `server`
     * : (dependency) Server config
     *
     * @var array
     *
     */
    protected $_Sungrazr_Controller_Service = array(
        'server_request'    => array(
            'class'         => 'Solar_Request',
            'spec'          => null,
        ),
        'server'            => null,
    );

    /**
     *
     * Flag to enable checking for fault responses.
     *
     * @var bool
     *
     */
    public $isFault;

    /**
     *
     * Fault details.
     *
     * @var array
     *
     */
    protected $_fault = array(
        'message' => null,
        'code' => 0
    );

    /**
     *
     * The Server controller.
     *
     * @var Sungrazr_Controller_Server
     *
     */
    protected $_server;

    /**
     *
     * The server's Solar_Request dependency
     *
     * @var Solar_Request
     *
     */
    protected $_request;

    /**
     *
     * Web Services request type, picked from last segment of the caller's
     * class name and lowercased. (Ex: jsonrpc, xmlrpc, rest, etc.)
     *
     * @var string
     *
     */
    protected $_type;

    /**
     *
     * The front-controller object (if any) that invoked this page-controller.
     *
     * @var Solar_Controller_Front
     *
     */
    protected $_front;

    /**
     *
     * Constructor.
     *
     * @param array $config User-provided configuration values.
     *
     */
    public function __construct($config = null)
    {
        parent::__construct($config);

        // set up request dependency
        if (! Solar_Registry::exists('server_request')) {
            Solar_Registry::set(
                'server_request',
                $this->_config['server_request']['class'],
                $this->_config['server_request']['spec']
            );
        }
        $this->_request = Solar_Registry::get('server_request');

        // Setup the server.
        $config = (array) $this->_config['server'];
        $config['caller'] = $this;
        $this->_server = Solar::factory('Sungrazr_Controller_Server', $config);

        // Set the server type.
        $parts = explode('_', get_class($this->_server));
        $last = end($parts);
        $this->_type = strtolower($last);

        // Add this class to the api.
        $this->_server->addClass(get_class($this));

        // special setup
        $this->_setup();
    }

    /**
     *
     * Post-construction setup.
     *
     * @return void
     *
     */
    protected function _setup()
    {
    }

    /**
     *
     * Executes the requested action and returns its output with layout.
     *
     * If an exception is thrown during the fetch() process, it is caught
     * and sent along to the _rescueException() method, which may generate
     * and return alternative output.
     *
     * @param string $spec The action specification string, for example,
     * "tags/php+framework" or "user/pmjones/php+framework?page=3"
     *
     * @return Solar_Http_Response A response object with headers and body from
     * the action, view, and layout.
     *
     */
    public function fetch($spec = null)
    {
        return $this->_server->fetch();
    }

    /**
     *
     * Return the fault
     *
     * @return array
     *
     */
    public function getFault()
    {
        return $this->_fault;
    }

    /**
     *
     * Raise the fault flag.
     *
     * @param mixed $fault A description of the fault. Will be serialized by
     * the server adapter before it is returned.
     *
     * @param int $code Fault code
     *
     * @return void
     *
     */
    protected function _raiseFault($fault = null, $code = 404)
    {
        $this->isFault = true;
        $this->_fault['message'] = $fault;
        $this->_fault['code'] = (int) $code;
    }

    /**
     *
     * Clear the fault.
     *
     * @return void
     *
     */
    protected function _clearFault()
    {
        $this->isFault = false;
        $this->_fault['message'] = null;
        $this->_fault['code'] = 0;
    }

    /**
     *
     * Injects the front-controller object that invoked this page-controller.
     *
     * @param Solar_Controller_Front $front The front-controller.
     *
     * @return void
     *
     */
    public function setFrontController($front)
    {
        $this->_front = $front;
    }
}