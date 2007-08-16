<?php
/**
 * 
 * Growl log adapter for Solar
 * 
 * @category Lux
 * 
 * @package Lux_Log
 * 
 * @subpackage Lux_Log_Adapter_Growl
 * 
 * @author Antti Holvikari <anttih@gmail.com>
 * 
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 * 
 */

/**
 * 
 * Growl log adapter for Solar
 * 
 * @category Lux
 * 
 * @package Lux_Log
 * 
 * @subpackage Lux_Log_Adapter_Growl
 * 
 */
class Lux_Log_Adapter_Growl extends Solar_Log_Adapter {
    
    /**
     * 
     * Config keys
     * 
     * `growl`
     * : (string|object) A registry key or an instance of Lux_Growl
     * 
     * @var array
     * 
     */
    protected $_Lux_Log_Adapter_Growl = array(
        'growl' => null,
    );
    
    /**
     * 
     * Constructor
     * 
     * @return array Config keys
     * 
     */
    public function __construct($config = array())
    {
        parent::__construct($config);
        
        // get Growl object with dependency injection
        $this->_growl = Solar::dependency('Lux_Growl', $this->_config['growl']);
    }
    
    /**
     * 
     * Sends a notification to Growl server/daemon
     * 
     * @return bool
     * 
     */
    protected function _save($class, $event, $descr)
    {
        return $this->_growl->notify($event, $class, $descr);
    }
}
