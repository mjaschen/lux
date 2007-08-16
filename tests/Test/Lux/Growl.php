<?php
/**
 * 
 * You should see some Growl messages when running these tests.
 * It is the *real* test :)
 * 
 */
class Test_Lux_Growl extends Solar_Test {
    
    private $_growl;
    
    public function setup()
    {
        $this->_growl = Solar::factory('Lux_Growl');
    }
    
    public function testNotify()
    {
        $out = $this->_growl->notify(
            'notice',
            'Title',
            'Description - This is a longer description of the notification'
        );
        
        $this->assertTrue($out);
    }
    
    public function testNotifyWithoutReRegistering()
    {
        $this->_growl = Solar::factory('Lux_Growl', array('register' => false));
        $out = $this->_growl->notify(
            'notice',
            'Testing',
            'This is a notification without re-registering'
        );
        
        $this->assertTrue($out);
    }
    
    public function testStickyNotify()
    {
        $this->_growl->notify(
            'notice',
            'Testing Sticky',
            'This should be a sticky notification',
            array('sticky' => true)
        );
    }
    
    public function testConnectWithPassword()
    {
        $config = array(
            'passwd' => 'test',
        );
        
        $this->_growl = Solar::factory('Lux_Growl', $config);
        $out = $this->_growl->notify(
            'notice',
            'Password test',
            'This connection was made with a password'
        );
        
        $this->assertTrue($out);
    }
}