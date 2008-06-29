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
     * undocumented class variable
     * 
     * @var string
     * 
     */
    public $name;
    
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
        return $this->_fetch('put', 200);
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
        return $this->_fetch('delete', 204);
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
            $this->_fetch('head', 200, array('max-keys' => 0));
            
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
    public function fetchKeys()
    {
        // make a GET request and expect 200 OK
        $response = $this->_fetch('get', 200);
        
        $xml = new SimpleXMLElement($response->getContent());
        
        // go through each key and collect info
        $keys = array();
        foreach ($xml->Contents as $key) {
            $data = array();
            foreach ($key->children() as $elem) {
                $name = strtolower($elem->getName());
                if ($name == 'owner') {
                    $data['owner'] = array(
                        'id'           => (string) $elem->ID,
                        'display_name' => (string) $elem->DisplayName,
                    );
                    continue;
                }
                
                // just add the value
                $data[$name] = (string) $elem;
            }
            
            // add one key
            $keys[] = $data;
        }
        
        return $keys;
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
        return $this->name;
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
    public function getHeaders($method)
    {
        $method = strtolower($method);
        
        // always send these headers
        $headers = $this->_headers;
        
        if ($method == 'put') {
            // add acl header
            $headers['x-amz-acl'] = $this->acl;
        }
        
        return $headers;
    }
}
