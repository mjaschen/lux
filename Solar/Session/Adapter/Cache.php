<?php
/**
 * 
 * Session adapter that uses Solar_Cache
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
 * Session adapter that uses Solar_Cache
 * 
 * @category Solar
 * 
 * @package Solar_Session
 * 
 * @author Antti Holvikari <anttih@gmail.com>
 * 
 */
class Solar_Session_Adapter_Cache extends Solar_Session_Adapter {
    
    /**
     * 
     * undocumented class variable
     * 
     * @var string
     * 
     */
    protected $_Solar_Session_Adapter_Cache = array(
        'cache' => array(
            'adapter' => 'Solar_Cache_Adapter_File',
        ),
    );
    
    /**
     * 
     * Solar_Cache object
     * 
     * @var Solar_Cache_Adapter
     * 
     */
    protected $_cache;
    
    /**
     * 
     * undocumented function
     * 
     * @return void
     * 
     */
    public function __constructor($config = null)
    {
        parent::__constructor($config);
        
        // set cache life to the same as cookie lifetime
        $this->_config['cache']['life'] = $this->_config['lifetime'];
    }
    
    /**
     * 
     * undocumented function
     * 
     * @return void
     * 
     */
    public function open()
    {
        // instantiate a cache object
        $this->_cache = Solar::dependency('Solar_Cache', $this->_config['cache']);
        return true;
    }
    
    /**
     * 
     * undocumented function
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
     * undocumented function
     * 
     * @return void
     * 
     */
    public function read($sid)
    {
        return $this->_cache->fetch($sid);
    }
    
    /**
     * 
     * undocumented function
     * 
     * @return void
     * 
     */
    public function write($sid, $data)
    {
        $this->_cache->save($sid, $data);
        return true;
    }
    
    /**
     * 
     * undocumented function
     * 
     * @return void
     * 
     */
    public function destroy($sid)
    {
        $this->_cache->delete($sid);
        return true;
    }
    
    /**
     * 
     * undocumented function
     * 
     * @return void
     * 
     */
    public function gc($lifetime)
    {
        return true;
    }
}