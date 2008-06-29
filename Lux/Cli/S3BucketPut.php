<?php
class Lux_Cli_S3BucketPut extends Lux_Cli_S3Base
{
    protected function _exec($bucket = null)
    {
        if (empty($bucket)) {
            return $this->_errln("You must specify a bucket name.");
        }
        
        $bucket_obj = $this->_s3->getBucket($bucket);
        
        try {
            
            $bucket_obj->acl = 'public-read';
            $bucket_obj->save();
            
            // try to fetch buckets
            $buckets = $this->_s3->fetchBuckets();
            
        } catch (Lux_Service_Amazon_S3_Exception $e) {
            return $this->_error($e);
        }
        
        $this->_outln('Buckets:');
        foreach ($buckets as $bucket) {
            $this->_outln("* {$bucket->name}");
        }
    }
}