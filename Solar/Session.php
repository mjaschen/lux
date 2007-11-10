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
 * Class for working with the $_SESSION array, including read-once
 * flashes.
 * 
 * On instantiation, starts a session if one has not yet been started.
 * 
 * @category Solar
 * 
 * @package Solar
 * 
 */
class Solar_Session extends Solar_Base {
    
    /**
     * 
     * User-defined configuration values.
     * 
     * Keys are ...
     * 
     * `adapter`
     * : Session adapter which should be used
     * 
     * @var array
     * 
     */
    protected $_Solar_Session = array(
        'adapter' => 'Solar_Session_Adapter_Phpsession',
    );
    
    /**
     * 
     * Solar factory
     * 
     * @return void
     * 
     */
    public function solarFactory()
    {
        // bring in the config and get the adapter class.
        $config = $this->_config;
        $class = $config['adapter'];
        unset($config['adapter']);
        
        return Solar::factory($class, $config);
    }
}
