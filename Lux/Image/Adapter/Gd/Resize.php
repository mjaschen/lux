<?php
/**
 *
 * Processor for image resizing.
 *
 * @category Lux
 *
 * @package Lux_Image
 *
 * @subpackage Lux_Image_Gd
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
 * Processor for image resizing.
 *
 * @category Lux
 *
 * @package Lux_Image
 *
 * @subpackage Lux_Image_Gd
 *
 */
class Lux_Image_Adapter_Gd_Resize extends Lux_Image_Processor {

    /**
     *
     * User-provided configuration values.
     *
     * @var array
     *
     */
    protected $_Lux_Image_Adapter_Gd_Resize = array(
        'quality' => 100,
        'bg'      => array(0, 0, 0),
    );

    /**
     *
     * Resizes a image.
     *
     * Resizes an image to size specified with $width and
     * $height. If either of those values is set to empty,
     * the empty value's size is calculated based on the proportion
     * of it in the original image. For example, when an image that
     * is 100x50 pixels is resized with resize(null, 20), the
     * resulting size will be 40x20.
     *
     * @param int $width Destination width, in pixels.
     *
     * @param int $height Destination height, in pixels.
     *
     * @param array $options Resize options.
     *
     * @return array Array with two elements; resulting width and height.
     *
     */
    public function resize($width, $height, $options = array())
    {
        // set options
        $options = array_merge($this->_config, $options);
        $info = $this->_image->getInfo();

        // should we count proportional sizes?
        if (empty($width) xor empty($height)) {
            $width_height = $info['width'] / $info['height'];

            if (empty($height)) {
                $height = round($width / $width_height);
            } else {
                $width = round($height * $width_height);
            }
        }

        // creates new image resource
        $this->_image->create($width, $height, $options['bg']);

        // @todo Depending on the image type, use imagecopyresized().
        imagecopyresampled(
            $this->_image->getTargetHandle(), // target image handle
            $this->_image->getHandle(),       // source image handle
            0, 0,                             // destination coordinates x, y
            0, 0,                             // source coordinates x, y
            $width, $height,                  // destination width, height
            $info['width'],                   // source width
            $info['height']                   // source height
        );

        // Set source handle to the new image handle.
        $this->_image->setHandle($this->_image->getTargetHandle());

        // return file sizes
        return array($width, $height);
    }
}