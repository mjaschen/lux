<?php
/**
 * 
 * Class for working with the $_SESSION array, including read-once
 * flashes.
 * 
 * @category Solar
 * 
 * @package Solar
 * 
 * @author Antti Holvikari <anttih@gmail.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id$
 * 
 */

/**
 * 
 * 
 * @category Solar
 * 
 * @package Solar
 * 
 */
abstract class Solar_Session_Adapter extends Solar_Base {
    
    /**
     * 
     * User-defined configuration values.
     * 
     * Keys are ...
     * 
     * `class`
     * : Store values in this top-level key in $_SESSION.  Default is
     *   'Solar'.
     * 
     * `lifetime`
     * : (int) Cookie lifetime in seconds. Set this far in the future to
     *   create a persistent session.
     * 
     * `path`
     * : Cookie path.
     * 
     * `domain`
     * : The domain in which this cookie is valid
     * 
     * `secure`
     * : (bool) Send the cookie **only** when using SSL
     * 
     * `httponly`
     * : (bool) HTTP-only cookie
     * 
     * @var array
     * 
     */
    protected $_Solar_Session_Adapter = array(
        'class'    => 'Solar',
        'lifetime' => 0,
        'path'     => '/',
        'domain'   => null,
        'secure'   => false,
        'httponly' => false,
    );
    
    /**
     * 
     * Array of "normal" session keys and values.
     * 
     * Convenience reference to $_SESSION[$this->_class].
     * 
     * @var array
     * 
     */
    public $store;
    
    /**
     * 
     * Constructor.
     * 
     * @param array $config User-defined configuration values.
     * 
     */
    public function __construct($config = null)
    {
        parent::__construct($config);
        
        // set session save handler
        $this->_setSaveHandler();
        
        // set cookie params
        session_set_cookie_params(
            (int)    $this->_config['lifetime'],
            (string) $this->_config['path'],
            (string) $this->_config['domain'],
            (bool)   $this->_config['secure'],
            (bool)   $this->_config['httponly']
        );
        
        // start a session if one does not exist, but not if we're at
        // the command line.
        if (session_id() === '' && PHP_SAPI != 'cli') {
            session_start();
        }
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
        if (! headers_sent()) {
            session_regenerate_id(true);
        }
    }
    
    /**
     * 
     * Sets session save handler
     * 
     * @return void
     * 
     */
    protected function _setSaveHandler()
    {
        // set session save handler and return
        return session_set_save_handler(
            array($this, 'open'),
            array($this, 'close'),
            array($this, 'read'),
            array($this, 'write'),
            array($this, 'destroy'),
            array($this, 'gc')
        );
    }
    
    /**
     * 
     * Open session handler
     * 
     * @return void
     * 
     */
    public function open()
    {
        return true;
    }
    
    /**
     * 
     * Close session handler
     * 
     * @return void
     * 
     */
    public function close()
    {
        return true;
    }
    
    /**
     * 
     * Reads session data
     * 
     * @return string
     * 
     */
    public function read($key)
    {
        return $this->get($key);
    }
    
    /**
     * 
     * Writes session data
     * 
     * @return string
     * 
     */
    public function write($key, $val)
    {
        return '';
    }
    
    /**
     * 
     * Destroys session data
     * 
     * @return bool
     * 
     */
    public function destroy($key)
    {
        return true;
    }
    
    /**
     * 
     * Collects old session data
     * 
     * @return bool
     * 
     */
    public function gc($lifetime)
    {
        return true;
    }
}
