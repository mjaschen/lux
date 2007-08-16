<?php
/**
 *
 * Class for image manipulation.
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
 * Class for image manipulation.
 *
 * @category Lux
 *
 * @package Lux_Image
 *
 */
abstract class Lux_Image_Adapter extends Solar_Base {
    
    /**
     *
     * User-provided configuration.
     *
     * Keys are ...
     *
     * `suppress_warnings`
     * : (bool) Whether or not to suppress warnings during image operations.
     *
     * @var array
     *
     */
    protected $_Lux_Image_Adapter = array(
        'suppress_warnings' => true,
    );
    
    /**
     *
     * Maps [[IMAGETYPE constants | http://www.php.net/manual/en/ref.image.php]]
     * to a readable string.
     *
     * @var array
     *
     */
    protected $_types = array(
        IMAGETYPE_GIF     => 'gif',
        IMAGETYPE_JPEG    => 'jpeg',
        IMAGETYPE_PNG     => 'png',
        IMAGETYPE_SWF     => 'swf',
        IMAGETYPE_PSD     => 'psd',
        IMAGETYPE_BMP     => 'bmp',
        IMAGETYPE_WBMP    => 'wbmp',
        IMAGETYPE_XBM     => 'xbm',
        IMAGETYPE_TIFF_II => 'tiff_ii',
        IMAGETYPE_TIFF_MM => 'tiff_mm',
        IMAGETYPE_IFF     => 'iff',
        IMAGETYPE_JB2     => 'jb2',
        IMAGETYPE_JPC     => 'jpc',
        IMAGETYPE_JP2     => 'jp2',
        IMAGETYPE_JPX     => 'jpx',
        IMAGETYPE_SWC     => 'swc',
    );

    /**
     *
     * List of image types supported by the adapter.
     *
     * @var array
     *
     */
    protected $_supported_types = array();

    /**
     *
     * Stores info about the currently loaded image. Keys are...
     *
     * `path`
     * : (string) Path to the image file.
     *
     * `width`
     * : (int) Image width.
     *
     * `height`
     * : (int) Image height.
     *
     * `type`
     * : (string) Image type as defined in $this->_types.
     *
     * `tag`
     * : (string) A text string with the correct height="yyy" width="xxx" that
     *   can be used directly in an img tag.
     *
     * `bits`
     * : (int) Image height.
     *
     * `channels`
     * : (int) Images channels: 3 for RGB pictures and 4 for CMYK pictures.
     *
     * `mime`
     * : (string) The correspondent mime type of the image.
     *
     * @var array
     *
     */
    protected $_info;

    /**
     *
     * Source image resource identifier.
     *
     * @var resource
     *
     */
    protected $_handle;

    /**
     *
     * Target image resource identifier.
     *
     * @var resource
     *
     */
    protected $_target_handle;

    /**
     *
     * Creates an image handle from a file.
     *
     * @param string $path Path to the image file.
     *
     */
    public function load($path = null)
    {
        throw $this->_exception('ERR_METHOD_NOT_IMPLEMENTED', array(
            'method' => 'load',
        ));
    }

    /**
     *
     * Loads a image info using [[php::getimagesize() | ]].
     *
     * @param string $path Path to the image file.
     *
     * @return void
     *
     */
    public function loadInfo($path)
    {
        // Does the file exist?
        if (! file_exists($path) || ! is_readable($path)) {
            throw $this->_exception(
                'ERR_FILE_NOT_READABLE',
                array('path' => $path)
            );
        }

        if ($this->_config['suppress_warnings']) {
            $info = @getimagesize($path);
        } else {
            $info = getimagesize($path);
        }

        // Is it a valid info?
        if (! is_array($info)) {
            throw $this->_exception(
                'ERR_INVALID_IMAGE',
                array('path' => $path)
            );
        }

        if (! array_key_exists($info[2], $this->_types)) {
            throw $this->_exception(
                'ERR_IMAGETYPE_UNKNOWN',
                array('path' => $path, 'type' => $info[2])
            );
        }

        // Done!
        $this->_info = array(
            'path'     => $path,
            'width'    => $info[0],
            'height'   => $info[1],
            'type'     => $this->_types[$info[2]],
            'tag'      => $info[3],
            'bits'     => $info['bits'],
            'channels' => $info['channels'],
            'mime'     => $info['mime'],
        );
    }

    /**
     *
     * Saves the image.
     *
     * @param string $path Path to save the image file.
     *
     * @param string $type Destination image type. If not defined, will use the
     * same type set in $_info.
     *
     * @param int $quality Image quality.
     *
     * @return void
     *
     */
    public function save($path, $type = null, $quality = 75)
    {
        throw $this->_exception('ERR_METHOD_NOT_IMPLEMENTED', array(
            'method' => 'save',
        ));
    }

    /**
     *
     * Frees any memory associated with images ($_handle and $_target_handle).
     * If resources are are passed as parameters, will free all of them.
     *
     * @return void
     *
     */
    public function free()
    {
        throw $this->_exception('ERR_METHOD_NOT_IMPLEMENTED', array(
            'method' => 'free',
        ));
    }

    /**
     *
     * Resizes a image.
     *
     * @param int $width Destination width, in pixels.
     *
     * @param int $height Destination height, in pixels.
     *
     * @param array $options Resize options.
     *
     * @return void
     *
     */
    public function resize($width, $height, $options = array())
    {
        throw $this->_exception('ERR_METHOD_NOT_IMPLEMENTED', array(
            'method' => 'resize',
        ));
    }

    /**
     *
     * Returns a file info.
     *
     * @return array Image info.
     *
     */
    public function getInfo()
    {
        return $this->_info;
    }

    /**
     *
     * Returns a file handle.
     *
     * @return resource Image resource identifier.
     *
     */
    public function getHandle()
    {
        return $this->_handle;
    }

    /**
     *
     * Checks if the current image info is set and throws an exception if it is
     * not.
     *
     */
    protected function _checkInfo()
    {
        if (! $this->_info) {
            throw $this->_exception('ERR_IMAGE_INFO_NOT_LOADED');
        }
    }

    /**
     *
     * Checks if an image type is supported by the adapter and throws
     * an exception if it is not.
     *
     * @param string $type Type to check.
     *
     */
    protected function _checkType($type)
    {
        if (! in_array($type, $this->_supported_types)) {
            throw $this->_exception('ERR_IMAGETYPE_NOT_SUPPORTED', array(
                'type' => $type,
            ));
        }
    }
}