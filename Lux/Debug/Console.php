<?php
/**
 * 
 * Debugging console for gathering debugging information
 * from several sources and displaying them
 * 
 * @category Lux
 * 
 * @package Lux_Debug
 * 
 * @author Antti Holvikari <anttih@gmail.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 */
class Lux_Debug_Console extends Solar_Base {
    
    /**
     * 
     * Configuration keys
     * 
     * Keys are...
     * 
     * `sql`
     * : (dependency) Solar_Sql dependency
     * 
     * `timer`
     * : (dependency) Solar_Debug_Timer dependency
     * 
     * `partial`
     * : (string) Template partial name
     * 
     * @var array
     * 
     */
    protected $_Lux_Debug_Console = array(
        'sql'     => 'sql',
        'timer'   => 'timer',
        'partial' => 'debug',
    );
    
    /**
     * 
     * Debugging info
     * 
     * @var array
     * 
     */
    protected $_debug = array(
        'sql_profile'      => array(),
        'headers_request'  => array(),
        'headers_response' => array(),
        'method'           => null,
        'super'            => array(
            'get'    => array(),
            'post'   => array(),
            'cookie' => array(),
        ),
    );
    
    /**
     * 
     * Timer object
     * 
     * @var Solar_Debug_Timer
     * 
     */
    public $timer;
    
    /**
     * 
     * Constructor
     * 
     * @return void
     * 
     */
    public function __construct($config = null)
    {
        parent::__construct($config);
        
        // start timer before anything
        $this->timer = Solar::dependency(
            'Solar_Debug_Timer',
            $this->_config['timer']
        );
    }
    
    /**
     * 
     * Displays debug info
     * 
     * Stops timer, gathers debug info and displays
     * everything
     * 
     * @return void
     * 
     */
    public function display()
    {
        // stop timer and get profiling
        $this->_timer();
        
        // sql profiling
        $this->_sqlProfile();
        
        // superglobals
        $this->_superGlobals();
        
        // both request and response headers
        $this->_headers();
        
        // new view object
        $view = Solar::factory('Solar_View');
        
        $path = str_replace('_', DIRECTORY_SEPARATOR, get_class($this));
        $path .= DIRECTORY_SEPARATOR . 'View';
        
        $view->setTemplatePath($path);
        
        // generate output
        echo $view->partial(
            $this->_config['partial'],
            $this->_debug
        );
    }
    
    /**
     * 
     * Stops timer and gets profiling info
     * 
     * @return void
     * 
     */
    protected function _timer()
    {
        $this->timer->stop();
        $this->_debug['timer'] = $this->timer->profile();
    }
    
    /**
     * 
     * Get profiling from Solar_Sql
     * 
     * @return void
     * 
     */
    protected function _sqlProfile()
    {
        $sql = Solar::dependency('Solar_Sql', $this->_config['sql']);
        $this->_debug['sql_profile'] = $sql->getProfile();
    }
    
    /**
     * 
     * Gets superglobals
     * 
     * @return void
     * 
     */
    protected function _superGlobals()
    {
        $request = Solar_Registry::get('request');
        
        // get these
        $supers = array('get', 'post', 'cookie');
        foreach ($supers as $super) {
            $this->_debug['super'][$super] = $request->{$super};
        }
    }
    
    /**
     * 
     * Gets request and response headers
     * 
     * @return void
     * 
     */
    protected function _headers()
    {
        $request = Solar_Registry::get('request');
        
        // request headers
        $this->_debug['headers_request']  = $request->http;
        
        $headers = array();
        $list = headers_list();
        foreach ($list as $header) {
            $pos = strpos(':', $header);
            list($name, $value) = explode(':', $header);
            $headers[strtolower($name)] = $value;
        }
        $this->_debug['headers_response'] = $headers;
        
        // request method. i.e GET, POST
        $this->_debug['method'] = $request->server('REQUEST_METHOD');
    }
}
