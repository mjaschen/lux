<?php
class Lux_Cli_S3ObjectDelete extends Lux_Cli_S3Base
{
    protected function _exec($bucket = null, $key = null)
    {
        if (empty($bucket)) {
            return $this->_errln("You must specify a bucket name.");
        }
        
        if (empty($key)) {
            return $this->_errln("You must specify a name for the object.");
        }
        
        $bucket_obj = $this->_s3->getBucket($bucket);
        $object = $bucket_obj->getObject($key);
        
        try {
            
            // try to delete object
            $object->delete();
            
        } catch (Lux_Service_Amazon_S3_Exception $e) {
            return $this->_error($e);
        }
    }
}