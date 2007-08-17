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
class Lux_Image_Adapter_Gd extends Lux_Image_Adapter {

    /**
     *
     * List of image types supported by the adapter.
     *
     * @var array
     *
     */
    protected $_supported_types = array(
        'gif', 'jpeg', 'png', 'wbmp', 'xbm',
    );

    /**
     *
     * Constructor.
     *
     * @param array $config User-provided configuration values.
     *
     */
    public function __construct($config = null)
    {
        // Make sure we have GD available.
        if (! extension_loaded('gd') ) {
            throw $this->_exception(
                'ERR_EXTENSION_NOT_LOADED',
                array('extension' => 'gd')
            );
        }

        // We're ok.
        parent::__construct($config);
    }

    /**
     *
     * Creates an image handle from a file.
     *
     * @param string $path Path to the image file.
     *
     * @return void
     *
     */
    public function load($path = null)
    {
        // Clean up previous handles.
        $this->free();

        if ($path) {
            $this->loadInfo($path);
        } else {
            // Check if file info was loaded.
            $this->_checkInfo();
        }

        // Check if file type is supported.
        $this->_checkType($this->_info['type']);

        // Assemble the image function.
        $function = 'imagecreatefrom' . $this->_info['type'];

        // Create the image handle.
        $this->_handle = $function($this->_info['path']);

        if (! is_resource($this->_handle)) {
            throw $this->_exception(
                'ERR_IMAGE_NOT_LOADED',
                array('path' => $this->_info['path'])
            );
        }
    }

    /**
     *
     * Saves the image.
     *
     * @param string $path Destination path for the image.
     *
     * @param string $type Destination image type. If not defined, will use the
     * same type set in $_info.
     *
     * @param int $quality Image quality, used for 'jpeg' or 'png' types.
     * Default is 75 for 'jpeg' and 7 for 'png'.
     *
     * @return bool True on success or false on failure.
     *
     */
    public function save($path, $type = null, $quality = null)
    {
        // Check if file info was loaded.
        $this->_checkInfo();

        if (! $type) {
            $type = $this->_info['type'];
        }

        // Check if file type is supported.
        $this->_checkType($type);

        // Check if target handle was created.
        if (! is_resource($this->_target_handle)) {
            throw $this->_exception('ERR_INVALID_RESOURCE');
        }

        // check file or dir permissions
        if(! file_exists($path)) {
            $check_path = dirname($path);
        } else {
            $check_path = $path;
        }

        if (! is_writable($check_path)) {
            throw $this->_exception('ERR_FILE_NOT_WRITABLE', array(
                'path' => $path
            ));
        }

        switch ($type) {
            case 'jpeg':
                if (! $quality) {
                    $quality = 75;
                }
                $res = imagejpeg($this->_target_handle, $path, $quality);
                break;

            case 'png':
                if (! $quality) {
                    $quality = 7;
                }
                $res = imagepng($this->_target_handle, $path, $quality);
                break;

            default:
                // Assemble the image function.
                $function = 'image' . $type;
                $res = $function($this->_target_handle, $path);
        }

        return $res;
    }

    /**
     *
     * Frees any memory associated with images ($_handle and $_target_handle).
     * If resources are passed as parameters, will free all of them.
     *
     * @return void
     *
     */
    public function free()
    {
        if (is_resource($this->_handle)) {
            imagedestroy($this->_handle);
        }

        if (is_resource($this->_target_handle)) {
            imagedestroy($this->_target_handle);
        }

        $args = func_get_args();

        if (! empty($args)) {
            foreach ($args as $resource) {
                if (is_resource($resource)) {
                    imagedestroy($resource);
                }
            }
        }
    }

    /**
     *
     * Creates the target image handle and sets
     * a background color for it
     *
     * @param int $width Width in pixels
     *
     * @param int $height Height in pixels
     *
     * @return void
     *
     */
    public function create($width, $height, $rgb = array(0, 0, 0))
    {
        // create image
        $this->_target_handle = imagecreatetruecolor($width, $height);

        // set up params
        array_unshift($rgb, $this->_target_handle);

        // set background
        call_user_func_array('imagecolorallocate', $rgb);
    }
}