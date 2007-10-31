<?php
/**
 * 
 * Session adapter for native PHP sessions
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
 * Session adapter for native PHP sessions
 * 
 * @category Solar
 * 
 * @package Solar_Session
 * 
 * @author Antti Holvikari <anttih@gmail.com>
 * 
 */
class Solar_Session_Adapter_Phpsession extends Solar_Session_Adapter {
    
    /**
     * 
     * Sets session save handler
     * 
     * @return void
     * 
     */
    protected function _setSaveHandler()
    {
        // don't set session save handler;
        // instead let PHP handle everything by itself
    }
}