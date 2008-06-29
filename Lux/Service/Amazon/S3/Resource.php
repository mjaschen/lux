<?php
/**
 * 
 * Class representing different resources on Amazon
 * 
 * @category Lux
 * 
 * @package Lux_Service
 * 
 * @subpackage Lux_Service_Amazon_S3
 * 
 * @author Antti Holvikari <anttih@gmail.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 */
abstract class Lux_Service_Amazon_S3_Resource extends Solar_Base
{
    /**
     * 
     * User provided confguration values
     * 
     * `s3`
     * : (dependency) Dependency on Lux_Service_Amazon_S3
     * 
     * @var array
     * 
     */
    protected $_Lux_Service_Amazon_S3_Resource = array(
        's3' => null,
    );
    
    /**
     * 
     * Amazon S3
     * 
     * @var Lux_Service_Amazon_S3
     * 
     */
    protected $_s3;
    
    /**
     * 
     * HTTP Headers
     * 
     * @var array
     * 
     */
    protected $_headers = array();
    
    /**
     * 
     * Indicates the existance of this resource
     * at Amazon
     * 
     * @var bool
     * 
     */
    protected $_exists = false;
    
    /**
     * 
     * Constructor
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
     * Returns the bucket name
     * 
     * @return void
     * 
     */
    public function getBucketName() {
        return null;
    }
    
    /**
     * 
     * Gets request body for requests on this resource
     * 
     * @return void
     * 
     */
    abstract public function getBody();
    
    /**
     * 
     * Gets request headers for requests on this resource
     * 
     * @return void
     * 
     */
    abstract public function getHeaders();
    
    /**
     * 
     * Undocumented function
     * 
     * @return void
     * 
     */
    protected function _fetch($method, $expect = 200, $params = array())
    {
        return $this->_s3->fetch($method, $this, $expect, $params);
    }
}