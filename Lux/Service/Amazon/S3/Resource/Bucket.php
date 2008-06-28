<?php

class Lux_Service_Amazon_S3_Resource_Bucket extends Lux_Service_Amazon_S3_Resource
{
    /**
     * 
     * undocumented class variable
     * 
     * @var string
     * 
     */
    protected $_location;
    
    /**
     * 
     * Undocumented function
     * 
     * @return void
     * 
     */
    public function getObject($key)
    {
        $config = array(
            's3'     => $this->_s3,
            'bucket' => $this,
        );
        
        // new resource type object
        $object = Solar::factory(
            'Lux_Service_Amazon_S3_Resource_Object',
            $config
        );
        
        $object->key = $key;
        
        return $object;
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
    public function delete()
    {
        // make a DELETE request and expect 204 No Content
        return $this->_s3->fetch('delete', $this, 204);
    }
    
    /**
     * 
     * Undocumented function
     * 
     * @return void
     * 
     */
    public function exists()
    {
        try {
            
            // perform a HEAD request and expect 200 OK
            $this->_s3->fetch('head', $this, 200, array('max-keys' => 0));
            
        } catch (Lux_Service_Amazon_S3_Exception_AccessDenied $e) {
            // the bucket is owned by someone else
            $this->_exists = false;
        } catch (Lux_Service_Amazon_S3_Exception_NoSuchBucket $e) {
            // it's available
            $this->_exists = true;
        }
        
        return $this->_exists;
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
        if (! empty($this->_location)) {
            return $this->_location;
        }
        
        return null;
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
