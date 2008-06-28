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
     * Undocumented function
     * 
     * @return void
     * 
     */
    public function __construct($config)
    {
        parent::__construct($config);
        
        if ($this->_config['bucket'] instanceof Lux_Service_Amazon_S3_Bucket) {
            
            $this->_bucket = $this->_config['bucket'];
            
        } elseif (is_string($this->_config['bucket'])) {
            
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
        $this->s3->get($this);
        
        $this->_exists = true;
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
        // save this object
        $this->_s3->fetch('put', $this);
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
