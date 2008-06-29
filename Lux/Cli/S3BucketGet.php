<?php
class Lux_Cli_S3BucketGet extends Lux_Cli_S3Base
{
    protected function _exec($bucket = null)
    {
        if (empty($bucket)) {
            return $this->_errln("You must specify a bucket name.");
        }
        
        $bucket_obj = $this->_s3->getBucket($bucket);
        
        try {
            
            // try to fetch keys
            $keys = $bucket_obj->fetchKeys();
            
        } catch (Lux_Service_Amazon_S3_Exception $e) {
            return $this->_error($e);
        }
        
        $this->_outln("\nKeys in $bucket:");
        foreach ($keys as $key) {
            $this->_outln("* {$key['key']} {$key['size']}");
        }
    }
}