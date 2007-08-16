<?php
class Test_Lux_Log_Adapter_Growl extends Solar_Test {
    
    public function setup()
    {
        $config = array(
            'adapter' => 'Lux_Log_Adapter_Growl',
        );
        $this->_log = Solar::factory('Solar_Log', $config);
    }
    
    public function test_save()
    {
        $this->assertTrue($this->_log->save(
            get_class($this),
            'notice',
            'Logged something interesting'
        ));
    }
}