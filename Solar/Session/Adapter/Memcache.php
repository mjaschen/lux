<?php
/**
 * 
 * Session adapter using memcache
 * 
 * @category Solar
 * 
 * @package Solar_Session
 * 
 * @author Antti Holvikari <anttih@gmail.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 */

/**
 * 
 * Session adapter using memcache
 * 
 * @category Solar
 * 
 * @package Solar_Session
 * 
 * @author Antti Holvikari <anttih@gmail.com>
 * 
 */
class Solar_Session_Adapter_Memcache extends Solar_Session_Adapter {
    
    /**
     * 
     * Configuration keys
     * 
     * `save_path`
     * : (array) Array of arrays of memcached servers to add
     * @var string
     * 
     */
    protected $_Solar_Session_Adapter_Memcache = array(
        'save_path' => array(array(
            'host'             => '127.0.0.1',
            'port'             => 11211,
            'persistent'       => true,
            'weight'           => 1,
            'timeout'          => 1,
            'retry_interval'   => 15,
            'status'           => true,
            'failure_fallback' => null,
        )),
    );
    
    /**
     * 
     * Sets session save handler
     * 
     * @return void
     * 
     */
    protected function _setSaveHandler()
    {
        // make sure we have memcache available
        if (! extension_loaded('memcache')) {
            throw $this->_exception(
                'ERR_EXTENSION_NOT_LOADED',
                array('extension' => 'memcache')
            );
        }
        
        // set session save handler
        ini_set('session.save_handler', 'memcache');
        
        $save_path = array();
        
        foreach ((array) $this->_config['save_path'] as $path) {
            $uri = Solar::factory('Solar_Uri');
            
            $uri->scheme = 'tcp';
            $uri->host   = $path['host'];
            $uri->port   = $path['port'];
            
            unset($path['host'], $path['port']);
            
            // remaining are query parameters
            $uri->query  = $path;
            
            // create the full URI and append to the list of save_paths
            $save_path[] = $uri->get(true);
        }
        
        // set save path
        ini_set('session.save_path', implode(',', $save_path));
    }
}