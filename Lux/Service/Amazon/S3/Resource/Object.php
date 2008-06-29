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
    public function load()
    {
        $this->_fetch('get');
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
        return $this->_fetch('put', 200);
    }
    
    /**
     * 
     * Deletes this object
     * 
     * @return void
     * 
     */
    public function delete()
    {
        return $this->_fetch('delete', 204);
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
    public function getHeaders($method)
    {
        $method = strtolower($method);
        
        // always send these headers
        $headers = $this->_headers;
        
        if ($method == 'put') {
            // add x-amz-meta-* headers
            $headers = array_merge($headers, $this->_getMetaHeaders());
            
            // add acl header
            $headers['x-amz-acl'] = $this->acl;
        }
        
        return $headers;
    }
}
