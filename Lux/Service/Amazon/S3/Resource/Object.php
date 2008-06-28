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
class Lux_Service_Amazon_S3_Resource_Object extends Lux_Service_Amazon_S3_Resource {
    
    /**
     * 
     * undocumented class variable
     * 
     * @var string
     * 
     */
    protected $_Lux_Service_Amazon_S3_Object = array(
        'bucket' => null,
    );
    
    /**
     * 
     * undocumented class variable
     * 
     * @var string
     * 
     */
    protected $_bucket;
    
    /**
     * 
     * undocumented class variable
     * 
     * @var string
     * 
     */
    public $key;
    
    /**
     * 
     * undocumented class variable
     * 
     * @var string
     * 
     */
    public $content;
    
    /**
     * 
     * Undocumented function
     * 
     * @return void
     * 
     */
    public function __construct($config)
    {
        parent::__construct($config);
        
        // bucket given as an object?
        if ($this->_config['bucket'] instanceof
            Lux_Service_Amazon_S3_Resource_Bucket) {
                
            // set bucket from config
            $this->_bucket = $this->_config['bucket'];
            
        } elseif (is_string($this->_config['bucket'])) {
            // bucket given as a string.
            // create a new bucket object.
            $this->_bucket = Solar::factory(
                'Lux_Service_Amazon_S3_Bucket',
                array('name' => $this->_config['bucket'])
            );
        }
    }
    
    /**
     * 
     * Undocumented function
     * 
     * @return void
     * 
     */
    public function save()
    {
        // make a PUT request and expect 200 OK
        return $this->_s3->fetch('put', $this, 200);
    }
    
    /**
     * 
     * Undocumented function
     * 
     * @return void
     * 
     */
    public function getBucketName()
    {
        return $this->_bucket->getBucketName();
    }
    
    /**
     * 
     * Undocumented function
     * 
     * @return void
     * 
     */
    public function getBody()
    {
        return $this->content;
    }
    
    /**
     * 
     * Undocumented function
     * 
     * @return void
     * 
     */
    public function getHeaders()
    {
        return $this->_headers;
    }
}
