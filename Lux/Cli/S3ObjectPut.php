<?php
class Lux_Cli_S3ObjectPut extends Lux_Cli_S3Base
{
    protected function _exec($bucket = null, $key = null, $file = null)
    {
        if (empty($bucket)) {
            return $this->_errln("You must specify a bucket name.");
        }
        
        if (empty($key)) {
            return $this->_errln("You must specify a name for the object.");
        }
        
        if (! empty($file) && ! file_exists($file)) {
            return $this->_errln("File not found.");
        }
        
        $bucket_obj = $this->_s3->getBucket($bucket);
        $object = $bucket_obj->getObject($key);
        
        try {
            
            $object->acl = 'public-read';
            $object->content = file_get_contents($file);
            $object->save();
            
        } catch (Lux_Service_Amazon_S3_Exception $e) {
            return $this->_error($e);
        }
    }
}