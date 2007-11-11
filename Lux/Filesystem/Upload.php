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
        'file'        => 'userfile',
        'extensions'  => array(),
        'max_size'    => 40960,
        'permission'  => 0444,
    );
    
    /**
     * 
     * Invalidation feedback messages
     * 
     * @var array
     * 
     */
    protected $_invalid = array();
    
    /**
     *
     * Info from $_FILES
     *
     * @var array
     *
     */
    public $file;
    
    /**
     * 
     * Validates upload and sets the environment
     * for further file processing
     * 
     * @return bool true is validation succeeds; false if
     * fails
     * 
     */
    public function validate($key = null)
    {
        // get the request object
        $request = Solar::factory('Solar_Request');
        
        $file = $this->_config['file'];
        if (! empty($key)) {
            $file = $key;
        }
        
        // get info from $_FILES
        $this->file = $request->files($file, false);
        
        // proceed with validations?
        if($this->file && is_uploaded_file($this->file['tmp_name'])) {
            
            $filesystem = Solar::factory($this->_config['filesystem']);
            
            // set file extension
            $this->_extension = $filesystem->getExtension($this->file['name']);
            
            // perform all validation
            $this->_validate();
            
        } else {
            // it was not even an uploaded file
            $this->_invalid[] = $this->locale('ERR_UPLOAD_FILE');
        }
        
        // invalids?
        if (! empty($this->_invalid)) {
            return false;
        }
        
        // all ok and we can proceed with the file
        return true;
    }
    
    /**
     *
     * Move file to destination
     *
     * @return void
     *
     */
    public function moveFile($dest)
    {
        // this will throw an exception
        $this->_checkDestination($dest);
        
        // attempt to move
        if(@move_uploaded_file($this->file['tmp_name'], $target)) {
            @chmod($dest, $this->_config['permission']);
        } else {
            throw $this->_exception('ERR_MOVE');
        }
    }
    
    /**
     * 
     * Returns invalidation feedback messages
     * 
     * @return void
     * 
     */
    public function getInvalid()
    {
        return $this->_invalid;
    }
    
    /**
     * 
     * Checks if the destination file is writable and
     * the file can be wrote to it
     * 
     * @return void
     * 
     */
    protected function _checkDestination($dest)
    {
        if (! is_writable($dest)) {
            throw $this->_exception('ERR_DIR_PERMISSIONS', $dest);
        }
    }
    
    // -----------------------------------------------------------------
    //
    // Validation
    //
    // -----------------------------------------------------------------
    
    /**
     * 
     * Validates uploaded file and sets feedback
     * 
     * @return void
     * 
     */
    protected function _validate()
    {
        // validate extension, mime-type and size
        $this->_validateExtension();
        $this->_validateMimeType();
        $this->_validateSize();
    }
    
    /**
     *
     * Validates file extension
     *
     * Throws an exception if it doesn't validate.
     *
     */
    protected function _validateExtension()
    {
        if (! isset($this->_config['extensions'][$this->_extension])) {
            $this->_invalid[] = $this->locale('ERR_FILE_EXTENSION');
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
        $ext = array_key_exists($this->_extension, $this->_config['extensions']);
        
        if (! $ext
        || ! in_array(
            $this->file['type'],
            $this->_config['extensions'][$this->_extension])) {
                
            $this->_invalid[] = $this->locale('ERR_FILE_TYPE');
        }
    }
    
    /**
     * 
     * Validates that file size is equal to or smaller than
     * set in the config key `max_size`
     * 
     * @return void
     * 
     */
    protected function _validateSize()
    {
        if ($this->file['size'] > $this->_config['max_size']) {
            $this->_invalid[] = $this->locale('ERR_FILE_SIZE');
        }
    }
}
