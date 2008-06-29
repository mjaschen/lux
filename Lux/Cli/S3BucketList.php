<?php
class Lux_Cli_S3BucketList extends Lux_Cli_S3Base
{
    protected function _exec()
    {
        try {
            
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