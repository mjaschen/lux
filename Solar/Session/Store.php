<?php
/**
 * 
 * Class for working with the $_SESSION array, including read-once
 * flashes.
 * 
 * @category Solar
 * 
 * @package Solar_Session
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @author Antti Holvikari <anttih@gmail.com>
 * 
 */

/**
 * 
 * Class for working with the $_SESSION array, including read-once
 * flashes.
 * 
 * Instantiate this once for each class that wants access to $_SESSION
 * values.  It automatically segments $_SESSION by class name, so be 
 * sure to use setClass() (or the 'class' config key) to identify the
 * segment properly.
 * 
 * A "flash" is a session value that propagates only until it is read,
 * at which time it is removed from the session.  Taken from ideas 
 * popularized by Ruby on Rails, this is useful for forwarding
 * information and messages between page loads without using GET vars
 * or cookies.
 * 
 * @category Solar
 * 
 * @package Solar
 * 
 * @author Antti Holvikari <anttih@gmail.com>
 * 
 */
class Solar_Session_Store extends Solar_Base {
    
    /**
     * 
     * Configuration keys
     * 
     * @var string
     * 
     */
    protected $_Solar_Session_Store = array(
        'class'   => 'Solar',
        'session' => 'session',
    );
    
    /**
     * 
     * Session class
     * 
     * @var Solar_Session_Adapter
     * 
     */
    public $session;
    
    /**
     * 
     * A reference to $_SESSION['class']
     * 
     * @var array
     * 
     */
    public $store;
    
    /**
     * 
     * Session object
     * 
     * @var Solar_Session_Adapter
     * 
     */
    public $flash;
    
    /**
     * 
     * A convenience reference to $_SESSION
     * 
     * @var Solar_Session_Adapter
     * 
     */
    public $_store;
    
    /**
     * 
     * The top-level $this->store class key for segmenting values.
     * 
     * @var string
     * 
     */
    protected $_class = 'Solar';
    
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
        
        $this->session = Solar::dependency(
            'Solar_Session',
            $this->_config['session']
        );
        
        $this->_class = trim($this->_config['class']);
        if ($this->_class == '') {
            $this->_class = 'Solar';
        }
        
        $this->_store =& $_SESSION;
        
        $this->setClass($this->_class);
    }
    
    /**
     * 
     * Sets the class segment for $this->store.
     * 
     * @param string $class The class name to segment by.
     * 
     * @return void
     * 
     */
    public function setClass($class)
    {
        $this->_class = $class;
        
        // set up the value store
        if (empty($this->store[$this->_class])) {
            $this->store[$this->_class] = array();
        }
        
        $this->store =& $this->_store[$this->_class];
        
        // set up the flash store
        if (empty($this->store[$this->_class]['flash'])) {
            $this->store[$this->_class]['flash'] = array();
        }
        
        $this->flash =& $this->store[$this->_class]['flash'];
    }
    
    /**
     * 
     * Sets a normal value by key.
     * 
     * @param string $key The data key.
     * 
     * @param mixed $val The value for the key; previous values will
     * be overwritten.
     * 
     * @return void
     * 
     */
    public function set($key, $val)
    {
        $this->store[$key] = $val;
    }
    
    /**
     * 
     * Appends a normal value to a key.
     * 
     * @param string $key The data key.
     * 
     * @param mixed $val The value to add to the key; this will
     * result in the value becoming an array.
     * 
     * @return void
     * 
     */
    public function add($key, $val)
    {
        if (! isset($this->store[$key])) {
            $this->store[$key] = array();
        }
        
        if (! is_array($this->store[$key])) {
            settype($this->store[$key], 'array');
        }
        
        $this->store[$key][] = $val;
    }
    
    /**
     * 
     * Gets a normal value by key, or an alternative default value if
     * the key does not exist.
     * 
     * @param string $key The data key.
     * 
     * @param mixed $val If key does not exist, returns this value
     * instead.  Default null.
     * 
     * @return mixed The value.
     * 
     */
    public function get($key, $val = null)
    {
        if (array_key_exists($key, $this->store)) {
            $val = $this->store[$key];
        }
        return $val;
    }
    
    /**
     * 
     * Resets (clears) all normal keys and values.
     * 
     * @return void
     * 
     */
    public function reset()
    {
        $this->store = array();
    }
    
    /**
     * 
     * Sets a flash value by key.
     * 
     * @param string $key The flash key.
     * 
     * @param mixed $val The value for the key; previous values will
     * be overwritten.
     * 
     * @return void
     * 
     */
    public function setFlash($key, $val)
    {
        $this->flash[$key] = $val;
    }
    
    /**
     * 
     * Appends a flash value to a key.
     * 
     * @param string $key The flash key.
     * 
     * @param mixed $val The flash value to add to the key; this will
     * result in the flash becoming an array.
     * 
     * @return void
     * 
     */
    public function addFlash($key, $val)
    {
        if (! isset($this->flash[$key])) {
            $this->flash[$key] = array();
        }
        
        if (! is_array($this->flash[$key])) {
            settype($this->flash[$key], 'array');
        }
        
        $this->flash[$key][] = $val;
    }
    
    /**
     * 
     * Gets a flash value by key, thereby removing the value.
     * 
     * @param string $key The flash key.
     * 
     * @param mixed $val If key does not exist, returns this value
     * instead.  Default null.
     * 
     * @return mixed The flash value.
     * 
     * @todo Mike Naberezny notes a possible issue with AJAX requests:
     * 
     *     // If this is an AJAX request, don't clear the flash.
     *     $headers = getallheaders();
     *     if (isset($headers['X-Requested-With']) &&
     *         stripos($headers['X-Requested-With'], 'xmlhttprequest') !== false) {
     *         // leave alone
     *         return;
     *     }
     * 
     * Would need to have Solar_Request access for this to work like the rest
     * of Solar does.
     * 
     */
    public function getFlash($key, $val = null)
    {
        if (array_key_exists($key, $this->flash)) {
            $val = $this->flash[$key];
            unset($this->flash[$key]);
        }
        return $val;
    }
    
    /**
     * 
     * Resets (clears) all flash keys and values.
     * 
     * @return void
     * 
     */
    public function resetFlash()
    {
        $this->flash = array();
    }
    
    /**
     * 
     * Resets both "normal" and "flash" values.
     * 
     * @return void
     * 
     */
    public function resetAll()
    {
        $this->reset();
        $this->resetFlash();
    }
    
    /**
     * 
     * Regenerates the session ID and deletes the previous session store.
     * 
     * Use this every time there is a privilege change.
     * 
     * @return void
     * 
     * @see [[php::session_regenerate_id()]]
     * 
     */
    public function regenerateId()
    {
        $this->session->regenerateId();
    }
}