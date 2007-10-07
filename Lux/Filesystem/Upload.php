<?php
/**
 *
 * Upload manager
 *
 * @category Lux
 *
 * @package Lux_Filesystem
 *
 * @subpackage Lux_Filesystem_Upload
 *
 * @author Rodrigo Moraes <rodrigo.moraes@gmail.com>
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
 * Upload manager
 *
 * @category Lux
 *
 * @package Lux_Filesystem
 *
 * @subpackage Lux_Filesystem_Upload
 *
 */
class Lux_Filesystem_Upload extends Solar_Base
{
    /**
     *
     * User-provided configuration.
     *
     * Keys are ...
     *
     * filesystem
     * : (string) Filesystem class name.
     *
     * `destination`
     * : (string) Destination path.
     *
     * `extensions`
     * : (array) A map with allowed extensions and the correspondent valid
     * mime-types. Example:
     *
     * {{code: php
     *     $valid  = array(
     *         'gif'  => 'image/gif',
     *         'jpg'  => array('image/jpeg', 'image/pjpeg'),
     *         'jpeg' => array('image/jpeg', 'image/pjpeg'),
     *         'png'  => 'image/x-png',
     *     );
     * }}
     *
     * `max_size`
     * : (int) Maximum allowed file size.
     *
     * `replace`
     * : (bool) Replace existent files?
     *
     * `permission`
     * : (int) Destination file permission (chmod value).
     *
     */
    protected $_Lux_Filesystem_Upload = array(
        'filesystem'  => 'Lux_Filesystem',
        'destination' => null,
        'extensions'  => array(),
        'max_size'    => 40960,
        'replace'     => false,
        'permission'  => 0444,
    );

    /**
     *
     * Posted file from $_FILE.
     *
     * @var array
     *
     */
    protected $_file;

    /**
     *
     * Destination file name.
     *
     * @var string
     *
     */
    protected $_file_name;

    /**
     *
     * Extracted file extension.
     *
     * @var string
     *
     */
    protected $_extension;

    /**
     *
     * Options for the uploaded file, merged with the config.
     *
     * @var array
     *
     */
    protected $_spec;

    /**
     *
     * Initializes the upload.
     *
     * Validates that the upload is infact an uploaded file
     * 
     * @param array $file Posted file from $_FILE.
     *
     * @param array $spec Options for the uploaded file, merged with the config.
     *
     * @return array Upload specification data.
     *
     */
    public function init($file, $spec = null)
    {
        // Sets the file and extension.
        $this->_setFile($file);

        // Set the specification.
        $this->_setSpec($spec);

        if(is_uploaded_file($this->_file['tmp_name'])) {
            // validate extension, mime-type and size
            $this->_validateExtension();
            $this->_validateMimeType();
            $this->_validateSize();
        }

        // Add the destination file name to the spec.
        $this->_spec['file_name'] = $this->_file_name;

        return $this->_spec;
    }

    /**
     *
     * Move file to destination
     *
     * @return void
     *
     */
    public function moveFile()
    {
        $this->_validateFile();
        $this->_validateDir();

        // Formats the file name.
        $this->_formatFileName();

        $destination = $this->_spec['destination']
                     . DIRECTORY_SEPARATOR
                     . $this->_file_name;

        // Move file and chmod.
        if(@move_uploaded_file($this->_file['tmp_name'], $destination)) {
            @chmod($destination, $this->_spec['permission']);
        } else {
            throw $this->_exception('ERR_MOVE');
        }
    }

    /**
     *
     * Get info about the upload
     *
     * @return void
     *
     */
    public function getTmpFileInfo()
    {
        return $this->_file;
    }

    /**
     *
     * Sets specifications for each uploaded file.
     *
     * @return void
     *
     */
    protected function _setSpec($spec = null)
    {
        if($spec) {
            $this->_spec = array_merge($this->_config, $spec);
        } else {
            $this->_spec = $this->_config;
        }

        // Convert extensions and myme-types to lower case.
        $ext = array();

        foreach($this->_spec['extensions'] as $name => $mime) {
            $ext[strtolower($name)] = array_map('strtolower', (array) $mime);
        }

        $this->_spec['extensions'] = $ext;
    }

    /**
     *
     * Sets the uploaded file and extracts its extension.
     *
     * @return void
     *
     */
    protected function _setFile($file)
    {
        $this->_file = $file;
        $filesystem = Solar::factory($this->_config['filesystem']);
        $this->_extension = strtolower($filesystem->getExtension($file['name']));
    }

    /**
     *
     * Formats a file name to be saved. By default sets as the uploaded file
     * name; extend this to add other possibilities.
     *
     * @return void
     *
     */
    protected function _formatFileName()
    {
        $this->_file_name = $this->_file['name'];
    }

    // -----------------------------------------------------------------
    //
    // Validation
    //
    // -----------------------------------------------------------------

    /**
     *
     * Validate uploaded file and destination directory.
     *
     * @return bool True if it passes all checkings.
     *
     */
    protected function _validate()
    {
        $this->_validateExtension();
        $this->_validateMimeType();
        $this->_validateSize();
        $this->_validateDir();
        $this->_validateFile();
    }

    /**
     *
     * Validates file extension.
     *
     * Throws an exception if it doesn't validate.
     *
     */
    protected function _validateExtension()
    {
        if(!isset($this->_spec['extensions'][$this->_extension])) {
            throw $this->_exception('ERR_FILE_EXTENSION', $this->_extension);
        }
    }

    /**
     *
     * Validates file mime type.
     *
     * Throws an exception if it doesn't validate.
     *
     */
    protected function _validateMimeType()
    {
        $mime_types = (array) $this->_spec['extensions'][$this->_extension];

        if(! in_array($this->_file['type'], $mime_types)) {
            throw $this->_exception('ERR_FILE_TYPE', $this->_file['type']);
        }
    }

    /**
     *
     * Validates file size.
     *
     * Throws an exception if it doesn't validate.
     *
     */
    protected function _validateSize()
    {
        if($this->_file['size'] > $this->_spec['max_size']) {
            throw $this->_exception('ERR_FILE_SIZE', $this->_file['size']);
        }
    }

    /**
     *
     * Validates that the destination directory exists and is writable.
     *
     * Throws an exception if it doesn't validate.
     *
     */
    protected function _validateDir()
    {
        $path = $this->_spec['destination'];

        if(!file_exists($path)) {
            throw $this->_exception('ERR_DIR_NOT_FOUND', $path);
        } elseif(!is_writable($path)) {
            throw $this->_exception('ERR_DIR_PERMISSIONS', $path);
        }
    }

    /**
     *
     * Validates, if the destination file exists, that it can be replaced
     * and is writable.
     *
     * Throws an exception if it doesn't validate.
     *
     */
    protected function _validateFile()
    {
        $path = $this->_spec['destination'] . DIRECTORY_SEPARATOR
            . $this->_file_name;

        if(file_exists($path)) {
            // Allow file replacement?
            if(!$this->_spec['replace']) {
                throw $this->_exception('ERR_FILE_EXISTS', $path);
            }
            // Has permission to replace existent file?
            elseif(!is_writable($path)) {
                throw $this->_exception('ERR_FILE_PERMISSION', $path);
            }
        }
    }
}
