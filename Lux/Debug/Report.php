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
class Lux_Debug_Report extends Solar_Base {
    
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
     * `view_path`
     * : (array) Add these paths to the template path stack
     * 
     * `partial`
     * : (string) Template partial name
     * 
     * @var array
     * 
     */
    protected $_Lux_Debug_Report = array(
        'sql'       => 'sql',
        'timer'     => 'timer',
        'view_path' => array(),
        'partial'   => 'debug',
    );
    
    /**
     * 
     * Debugging info
     * 
     * These keys get assigned to the view
     * 
     * @var array
     * 
     */
    protected $_debug = array(
        'sql_profile'      => array(),
        'timer'            => array(),
        'var_dump'         => array(),
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
     * Solar_Debug_Var object
     * 
     * @var Solar_Debug_Var
     * 
     */
    protected $_debug_var;
    
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
        
        $this->_debug_var = Solar::factory('Solar_Debug_Var');
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
        $this->_super();
        
        // both request and response headers
        $this->_headers();
        
        $view = $this->_setView();
        
        // generate output
        echo $view->partial(
            $this->_config['partial'],
            $this->_debug
        );
    }
    
    /**
     * 
     * Runs var_dump() on a variable and stores that
     * output to be displayed in the debugger
     * 
     * @param mixed $var Variable to be displayed
     * 
     * @param string $label Label for the variable
     * 
     * @return void
     * 
     */
    public function varDump($var, $label)
    {
        // put var_dump() output into an array with $label as the key
        $this->_debug['var_dump'][$label] = $this->_debug_var->fetch($var);
    }
    
    /**
     * 
     * Sets view object
     * 
     * Adds to template path stack and
     * sets class for getText helper.
     * 
     * @return Solar_View
     * 
     */
    protected function _setView()
    {
        // new view object
        $view = Solar::factory('Solar_View');
        $class = get_class($this);
        $path = str_replace('_', DIRECTORY_SEPARATOR, $class);
        $path .= DIRECTORY_SEPARATOR . 'View';
        
        // template path stack
        $paths   = (array) $this->_config['view_path'];
        $paths[] = $path;
        
        $view->setTemplatePath($paths);
        
        // set the locale class for the getText helper
        $view->getHelper('getTextRaw')->setClass($class);
        
        return $view;
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
    protected function _super()
    {
        $request = Solar_Registry::get('request');
        
        // get these
        $list = array('get', 'post', 'cookie');
        foreach ($list as $super) {
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
