<?php
/**
 *
 * Class for image manipulation
 *
 * @category Lux
 *
 * @package Lux_Image
 *
 * @author Antti Holvikari <anttih@gmail.com>
 *
 * @author Rodrigo Moraes <rodrigo.moraes@gmail.com>
 *
 * @version $Id$
 *
 */

/**
 *
 * Class for image manipulation
 *
 * @category Lux
 *
 * @package Lux_Image
 *
 */
class Lux_Image extends Solar_Base {

    /**
     *
     * Config keys.
     *
     * @var array
     *
     */
    protected $_Lux_Image = array(
        'adapter' => 'Lux_Image_Adapter_Gd',
    );

    /**
     *
     * Factory method for returning adapters.
     *
     * @return Lux_Image_Adapter
     *
     */
    public function solarFactory()
    {
        $class = $this->_config['adapter'];
        unset($this->_config['adapter']);
        return Solar::factory($class, $this->_config);
    }
}
