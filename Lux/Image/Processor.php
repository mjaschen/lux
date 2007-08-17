<?php
/**
 *
 * Abstract class for image processors.
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
 * Abstract class for image processors.
 *
 * @category Lux
 *
 * @package Lux_Image
 *
 */
abstract class Lux_Image_Processor extends Solar_Base {

    /**
     *
     * The image adapter.
     *
     * @var Lux_Image_Adapter
     *
     */
    protected $_image;

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

        if (empty($this->_config['_image']) ||
            ! $this->_config['_image'] instanceof Lux_Image_Adapter) {
            // We need the adapter object.
            throw Solar::exception(
                get_class($this),
                'ERR_IMAGE_ADAPTER_NOT_SET',
                "Config key '_image' not set, or not Lux_Image_Adapter object"
            );
        }

        $this->_image = $this->_config['_image'];
        unset($this->_config['_image']);
    }
}