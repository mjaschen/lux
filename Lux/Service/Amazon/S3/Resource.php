<?php
/**
 * 
 * undocumented class
 * 
 * @package default
 * 
 * @author Antti Holvikari <anttih@gmail.com>
 * 
 */
abstract class Lux_Service_Amazon_S3_Resource extends Solar_Base
{
    /**
     * 
     * undocumented class variable
     * 
     * @var string
     * 
     */
    protected $_Lux_Service_Amazon_S3_Resource = array(
        's3' => null,
    );
    
    /**
     * 
     * undocumented class variable
     * 
     * @var string
     * 
     */
    protected $_s3;
    
    /**
     * 
     * undocumented class variable
     * 
     * @var string
     * 
     */
    protected $_headers = array();
    
    /**
     * 
     * undocumented class variable
     * 
     * @var string
     * 
     */
    protected $_exists = false;
    
    /**
     * 
     * Undocumented function
     * 
     * @return void
     * 
     */
    public function __construct($config = null)
    {
        parent::__construct($config);
        
        // get S3 service object
        $this->_s3 = Solar::dependency(
            'Lux_Service_Amazon_S3',
            $this->_config['s3']
        );
    }
    
    /**
     * 
     * Undocumented function
     * 
     * @return void
     * 
     */
    public function getBucketName() {
        return null;
    }
    
    /**
     * 
     * Undocumented function
     * 
     * @return void
     * 
     */
    abstract public function getBody();
    
    /**
     * 
     * Undocumented function
     * 
     * @return void
     * 
     */
    abstract public function getHeaders();
    
}