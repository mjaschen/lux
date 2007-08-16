<?php
/**
 * 
 * Growl client
 * 
 * @category Lux
 * 
 * @package Lux_Growl
 * 
 * @author Bertrand Mansion <golgote@mamasam.com>
 * 
 * @author Antti Holvikari <anttih@gmail.com>
 * 
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 * 
 */

/**
 * 
 * Growl client
 * 
 * Every application that wants to send notifications to Growl
 * needs to register itself to the Growl server. Along with the
 * registration you need to pass it a list of notification types
 * that our application will use. This list is defined with the
 * 'messages' config key.
 * 
 * {@link http://growl.info/documentation/developer/protocol.php Growl protocol}
 * 
 * @category Lux
 * 
 * @package Lux_Growl
 * 
 * @link http://growl.info Growl Homepage
 * 
 */
class Lux_Growl extends Sungrazr_Socket_Client {
    
    /**
     * 
     * Growl protocol version
     * 
     * @var int
     * 
     */
    const GROWL_PROTOCOL_VERSION = 1;
    
    /**
     * 
     * Growl registration type
     * 
     * @var int
     * 
     */
    const GROWL_TYPE_REGISTRATION = 0;
    
    /**
     * 
     * Growl notification type
     * 
     * @var int
     * 
     */
    const GROWL_TYPE_NOTIFICATION = 1;
    
    /**
     * 
     * Config keys
     * 
     * `passwd`
     * : (string) Password for the Growl server
     * 
     * `app`
     * : (string) A short application name for this instance
     * 
     * `register`
     * : (bool) Force a re-register of this 'app' for Growl
     * 
     * `messages`
     * : (array) Available notifications for this 'app'. Every
     *   notification name needs to be one of the keys, otherwise
     *   Growl won't show them.
     * 
     * `options`
     * : (array) Default options for every notification.
     * 
     * `transport`
     * : (string) Transport method for the connection. 
     *   Default: 'udp'.
     * 
     * `target`
     * : (string) Hostname which to connect to
     * 
     * `port`
     * : (int) Port which to connect to
     * 
     * @var array
     * 
     */
    protected $_Lux_Growl = array(
        'passwd'   => null,
        'app'      => __CLASS__,
        'register' => true,
        'messages' => array(
            'notice' => array('enable' => true),
        ),
        'options' => array(
            'sticky'   => false,
            'priority' => 0,
        ),
        'transport' => 'udp',
        'target'    => '127.0.0.1',
        'port'      => 9887,
    );
    
    /**
     * 
     * Wether app is registered
     * 
     * @var bool
     * 
     */
    private $_registered = false;
    
    /**
     * 
     * Factory method to connect to Growl server
     * 
     * @return Lux_Growl or false on failure
     * 
     */
    public function solarFactory()
    {
        return $this->connect();
    }
    
    /**
     * 
     * Sends a notification to Growl
     * 
     * Sends a notification to Growl by first trying to
     * register the application.
     * 
     * @param string $name Name of the notification. All notification
     * names are defined in the `messages` config key; this must be 
     * one of them.
     * 
     * @param string $title Title of the notification
     * 
     * @param string $descr Longer notification message
     * 
     * @param array $opts Options for this notification
     * 
     * @return bool Boolean true on success
     * 
     */
    public function notify($name, $title, $descr, $opts = array())
    {
        // try to register
        $this->_register();
        
        // send notification
        $this->_notify($name, $title, $descr, $opts);
        
        // all done!
        return true;
    }
    
    /**
     * 
     * Registers application to Growl
     * 
     * Registers application object to Growl if registering
     * is enabled and app has not already been registered.
     * 
     * @return bool Boolean true on success or false
     * if app is already registered or registering is
     * disabled.
     * 
     */
    private function _register()
    {
        // if already registered or if registering is disabled
        // don't do anything and return false
        if ($this->_registered || $this->_config['register'] === false) {
            return false;
        }
        
        // application name
        $app_name = utf8_encode($this->_config['app']);
        
        // password for the growl server
        $passwd = $this->_config['passwd'];
        
        // initialize some vars
        $name_data     = '';
        $default_data  = '';
        $name_count    = 0;
        $default_count = 0;
        
        // get list of notifications and add them
        $notifications = $this->_config['messages'];
        foreach($notifications as $name => $options) {
            if (is_array($options) && !empty($options['enable'])) {
                $default_data .= pack('c', $name_count);
                ++$default_count;
            }
            
            $name = utf8_encode($name);
            $name_data .= pack('n', strlen($name)).$name;
            ++$name_count;
        }
        
        $data = pack(
            'c2nc2',
            self::GROWL_PROTOCOL_VERSION,
            self::GROWL_TYPE_REGISTRATION,
            strlen($app_name),
            $name_count,
            $default_count
        );
        
        $data .= $app_name . $name_data . $default_data;
        
        if (!empty($password)) {
            $checksum = pack('H32', md5($data . $passwd));
        } else {
            $checksum = pack('H32', md5($data));
        }
        $data .= $checksum;
        
        // write to stream
        $this->_write($data);
        
        // set register status
        $this->_registered = true;
        
        // success!
        return true;
    }
    
    /**
     * 
     * Sends a notification to Growl
     * 
     * @return bool
     * 
     */
    private function _notify($name, $title, $descr, $opts = array())
    {
        // merge options
        $opts = array_merge($this->_config['options'], (array) $opts);
        
        // app name
        $app = utf8_encode($this->_config['app']);
        
        // password
        $passwd = $this->_config['passwd'];
        
        // notification name
        $name = utf8_encode($name);
        
        // title of the notification
        $title = utf8_encode($title);
        
        // description
        $descr = utf8_encode($descr);
        
        // priority (from -2 to 2)
        $priority = $opts['priority'];
        
        $flags = ($priority & 7) * 2;
        
        if ($priority < 0) {
            $flags |= 8;
        }
        if ($opts['sticky'] === true) {
            $flags = $flags | 1;
        }
        
        // 
        $data = pack(
            'c2n5',
            self::GROWL_PROTOCOL_VERSION,
            self::GROWL_TYPE_NOTIFICATION,
            $flags,
            strlen($name),
            strlen($title),
            strlen($descr),
            strlen($app)
        );
        
        // add parts to data
        $data .= $name . $title . $descr . $app;
        
        // create checksum
        if (! empty($passwd)) {
            $checksum = pack('H32', md5($data . $passwd));
        } else {
            $checksum = pack('H32', md5($data));
        }
        
        $data .= $checksum;
        
        // write all data to stream
        $this->_write($data);
        
        return true;
    }
    
    /**
     * 
     * Gets list of available notification types
     * 
     * @return array Array or notifications you can use
     * with notify().
     * 
     */
    public function getNotifications()
    {
        return array_keys($this->_config['messages']);
    }
    
    /**
     * 
     * Writes data to stream
     * 
     * @return int|bool Number of bytes written of false
     * 
     */
    protected function _write($data)
    {
        // try to write the data
        $wrote = fwrite($this->_fp, $data, strlen($data));
        
        // failed, this is an error
        if ($wrote === false) {
            throw $this->_exception('ERR_SEND');
        }
        
        // return the number of bytes written
        return $wrote;
    }
}
