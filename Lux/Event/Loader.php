<?php
/**
 *
 * Factory class for event subscription loader adapters.
 *
 * @category Lux
 *
 * @package Lux_Event
 *
 * @author Rodrigo Moraes <rodrigo.moraes@gmail.com>
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 *
 * @version $Id$
 *
 */

/**
 *
 * Factory class for event subscription loader adapters.
 *
 * @category Lux
 *
 * @package Lux_Event
 *
 */
class Lux_Event_Loader extends Solar_Base
{
    /**
     *
     * User-provided configuration.
     *
     * Keys are ...
     *
     * `adapter`
     * : (string) The class to factory.  Default is
     * 'Lux_Event_Loader_Adapter_Config'.
     *
     * @var array
     *
     */
    protected $_Lux_Event_Loader = array(
        'adapter' => 'Lux_Event_Loader_Adapter_Config',
    );

    /**
     *
     * Factory method to create transport adapter objects.
     *
     * @return Lux_Event_Loader_Adapter
     *
     */
    public function solarFactory()
    {
        $class = $this->_config['adapter'];
        unset($this->_config['adapter']);
        return Solar::factory($class, $this->_config);
    }
}