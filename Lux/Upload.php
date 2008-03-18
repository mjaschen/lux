<?php
/**
 *
 * Upload manager. Usage example:
 *
 * {{code:php
 *
 * // Configure.
 * $config = array(
 *     'path'     => '/path/to/uploaded/files/',
 *     'allowed'  => array('png'  => array('image/x-png', 'image/png')),
 *     'max_size' => 105542,
 *     'chmod'    => 0644,
 * );
 *
 * // Start the upload object.
 * $upload = Solar::factory('Lux_Upload', $config);
 *
 * // Use the file info stored in $_FILES['image_upload'].
 * $upload->setFile('image_upload');
 *
 * // Save the file. The file will be saved in the configured destination dir
 * // as "test", adding the source extension to the provided file name.
 * $upload->moveFile('test');
 *
 * // Save another uploaded file...
 * $upload->setFile('image_upload_2');
 * $upload->moveFile('test_2');
 *
 * }}
 *
 * @category Lux
 *
 * @package Lux_Upload
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
class Lux_Upload extends Solar_Base
{
    /**
     *
     * User-provided configuration.
     *
     * Keys are ...
     *
     * `file`
     * : (string) Key to get from $_FILES. If defined in config, call setFile()
     *   automatically on construction.
     *
     * `path`
     * : (string) Destination path.
     *
     * `allowed`
     * : (array) A map with allowed extensions and the correspondent valid
     *   mime-types. Example:
     *
     * {{code: php
     *     $valid  = array(
     *         'gif'  => 'image/gif',
     *         'jpg'  => array('image/jpeg', 'image/pjpeg'),
     *         'jpeg' => array('image/jpeg', 'image/pjpeg'),
     *         'png'  => array('image/png', 'image/x-png'),
     *     );
     * }}
     *
     * `max_size`
     * : (int) Maximum allowed file size, in bytes. Default is 40960 (40 Kb).
     *   If null, allow files of any size.
     *
     * `replace`
     * : (bool) True to replace an existent file, if it already exists.
     *
     * `chmod`
     * : (int) Destination file permission (chmod value).
     *
     */
    protected $_Lux_Upload = array(
        'file'     => null,
        'path'     => null,
        'allowed'  => null,
        'max_size' => 40960,
        'replace'  => false,
        'chmod'    => 0444,
    );

    /**
     *
     * Current file data from $_FILES.
     *
     * @var array
     *
     */
    protected $_file;

    /**
     *
     * Uploaded file name, without extension.
     *
     * @var string
     *
     */
    protected $_filename;

    /**
     *
     * Extension from the uploaded file.
     *
     * @var string
     *
     */
    protected $_extension;

    /**
     *
     * Saved file name.
     *
     * @var string
     *
     */
    protected $_saved_filename;

    /**
     *
     * Constructor.
     *
     * @param array $config User-defined configuration values.
     *
     */
    public function __construct($config = null)
    {
        parent::__construct($config);

        // Set the file.
        if ($this->_config['file']) {
            $this->setFile($this->_config['file']);
        }

        // Convert extensions and mime-types to lower case.
        $ext = array();

        foreach ((array) $this->_config['allowed'] as $name => $mime) {
            $ext[strtolower($name)] = array_map('strtolower', (array) $mime);
        }

        $this->_config['allowed'] = $ext;
    }

    /**
     *
     * Sets the uploaded file and extracts its extension.
     *
     * @param array|string $key A item or a key from $_FILES.
     *
     */
    public function setFile($spec)
    {
        // Reset filename.
        $this->_saved_filename = null;

        if (is_array($spec)) {
            // It is a $_FILES spec.
            // @todo Should we check if it is valid?
            $this->_file = $spec;
        } elseif (is_string($spec)) {
            // Get the file info from $_FILES.
            $this->_file = Solar_Registry::get('request')->files($spec);
        }

        if (! $this->_file) {
            throw $this->_exception('ERR_FILE_NOT_SET', array('spec' => $spec));
        }

        // Get the file name and extension.
        $pos = strrpos($this->_file['name'], '.');
        $this->_filename = substr($this->_file['name'], 0, $pos);
        $this->_extension = strtolower(substr($this->_file['name'], $pos + 1));

        if (is_uploaded_file($this->_file['tmp_name'])) {
            // Validate extension, mime-type and size.
            $this->_validateExtension();
            $this->_validateMimeType();
            $this->_validateSize();
        }
    }

    /**
     *
     * Move file to destination.
     *
     * @param string $file Destination file name, without extension.
     *
     * @param string $dir Full path to the destination directory.
     *
     * @return bool True if the file was successfully uploaded, false otherwise.
     *
     */
    public function moveFile($name = null, $extension = null, $dir = null)
    {
        if (! $this->_file) {
            throw $this->_exception('ERR_FILE_NOT_SET');
        }

        // Use default dir and file name if they're not set.
        $name = $name ? $name : $this->_filename;
        $extension = $extension ? $extension : $this->_extension;
        $dir = $dir ? $dir : $this->_config['path'];

        // Build the full path to the destination file.
        $file = Solar_Dir::fix($dir) . $name . '.' . $extension;

        // Check if file name and dir are valid.
        $this->_validateDir($dir);
        $this->_validateFile($file);

        // Move file and chmod.
        $res = @move_uploaded_file($this->_file['tmp_name'], $file);

        if ($res) {
            $umask = umask(0000);
            @chmod($file, $this->_config['chmod']);
            umask($umask);

            $this->_saved_filename = $file;
        } else {
            throw $this->_exception('ERR_MOVE');
        }

        return $res;
    }

    /**
     *
     * Get info about the upload.
     *
     * @param string $key A key to get from the current file info.
     *
     * @return string|array The key info or the upload info array.
     *
     */
    public function getFileInfo($key = null)
    {
        if (! $this->_file) {
            throw $this->_exception('ERR_FILE_NOT_SET');
        }

        if ($key) {
            if (array_key_exists($key, $this->_file)) {
                return $this->_file[$key];
            } else {
                return null;
            }
        }

        return $this->_file;
    }

    /**
     *
     * Returns the filename as it was saved.
     *
     * @return string The saved filename.
     *
     */
    public function getFilename()
    {
        if ($this->_saved_filename) {
            return basename($this->_saved_filename);
        }
    }

    // -----------------------------------------------------------------
    //
    // Validation
    //
    // -----------------------------------------------------------------

    /**
     *
     * Validates file extension.
     *
     * Throws an exception if it doesn't validate.
     *
     */
    protected function _validateExtension()
    {
        if (! isset($this->_config['allowed'][$this->_extension])) {
            throw $this->_exception('ERR_FILE_EXTENSION', array(
                'file'      => $this->_file['name'],
                'extension' => $this->_extension,
            ));
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
        $mime_types = (array) $this->_config['allowed'][$this->_extension];

        if (! in_array($this->_file['type'], $mime_types)) {
            throw $this->_exception('ERR_FILE_TYPE', array(
                'file' => $this->_file['name'],
                'type' => $this->_file['type'],
            ));
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
        if ($this->_config['max_size'] &&
            $this->_file['size'] > $this->_config['max_size']) {
            throw $this->_exception('ERR_FILE_SIZE', array(
                'file' => $this->_file['name'],
                'size' => $this->_file['size'],
            ));
        }
    }

    /**
     *
     * Validates that the destination directory exists and is writable.
     *
     * Throws an exception if it doesn't validate.
     *
     */
    protected function _validateDir($dir)
    {
        if (! file_exists($dir)) {
            throw $this->_exception('ERR_DIR_NOT_FOUND', array(
                'file' => $this->_file['name'],
                'path' => $dir,
            ));
        } elseif (! is_writable($dir)) {
            throw $this->_exception('ERR_DIR_PERMISSIONS', array(
                'file' => $this->_file['name'],
                'path' => $dir,
            ));
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
    protected function _validateFile($file)
    {
        if (file_exists($file)) {
            // Allow file replacement?
            if (! $this->_config['replace']) {
                throw $this->_exception('ERR_FILE_EXISTS', array(
                    'file' => $this->_file['name'],
                    'path' => $file,
                ));
            }
            // Has permission to replace existent file?
            elseif (! is_writable($file)) {
                throw $this->_exception('ERR_FILE_PERMISSION', array(
                    'file' => $this->_file['name'],
                    'path' => $file,
                ));
            }
        }
    }
}