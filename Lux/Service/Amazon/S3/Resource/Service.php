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
class Lux_Service_Amazon_S3_Resource_Service extends Lux_Service_Amazon_S3_Resource
{
    /**
     * 
     * Undocumented function
     * 
     * @return void
     * 
     */
    public function fetchBuckets()
    {
        $response = $this->_fetch('get');
        
        $xml = new SimpleXMLElement($response->getContent());
        
        $buckets = array();
        foreach ($xml->Buckets->children() as $bucket) {
            $name = (string) $bucket->Name;
            $buckets[] = $this->_s3->getBucket($name);
        }
        
        return $buckets;
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
        return $this->_headers;
    }
}