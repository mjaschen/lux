<?php
abstract class Lux_Cli_S3Base extends Solar_Cli_Base
{
    /**
     * 
     * undocumented class variable
     * 
     * @var string
     * 
     */
    protected $_s3;
    
    /**
     * 
     * Undocumented function
     * 
     * @return void
     * 
     */
    protected function _error(Lux_Service_Amazon_S3_Exception $e)
    {
        $this->_errln('Error: ' . $e->getMessage());
        
        $info = $e->getInfo();
        foreach ($info as $key => $val) {
            $this->_errln("$key: $val");
        }
    }
    
    /**
     * 
     * undocumented class variable
     * 
     * @var string
     * 
     */
    protected function _preExec()
    {
        parent::_preExec();
        $this->_s3 = Solar::factory('Lux_Service_Amazon_S3');
    }
}