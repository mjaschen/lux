<?php
/**
 * 
 * Class for working with the $_SESSION array, including read-once
 * flashes.
 * 
 * @category Solar
 * 
 * @package Solar
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id: Session.php 2365 2007-03-19 14:19:44Z pmjones $
 * 
 */

/**
 * 
 * Class for working with the $_SESSION array, including read-once
 * flashes.
 * 
 * On instantiation, starts a session if one has not yet been started.
 * 
 * Instantiate this once for each class that wants access to $_SESSION
 * values.  It automatically segments $_SESSION by class name, so be 
 * sure to use setClass() (or the 'class' config key) to identify the
 * segment properly.
 * 
 * A "flash" is a session value that propagates only until it is read,
 * at which time it is removed from the session.  Taken from ideas 
 * popularized by Ruby on Rails, this is useful for forwarding
 * information and messages between page loads without using GET vars
 * or cookies.
 * 
 * @category Solar
 * 
 * @package Solar
 * 
 */
class Solar_Session extends Solar_Base {
    
    /**
     * 
     * User-defined configuration values.
     * 
     * Keys are ...
     * 
     * `class`
     * : Store values in this top-level key in $_SESSION.  Default is
     *   'Solar'.
     * 
     * @var array
     * 
     */
    protected $_Solar_Session = array(
        'adapter' => 'Solar_Session_Adapter_Phpsession',
    );
    
    /**
     * 
     * Constructor.
     * 
     * @param array $config User-defined configuration values.
     * 
     */
    public function __construct($config = null)
    {
        parent::__construct($config);
    }
    
    /**
     * 
     * undocumented function
     * 
     * @return void
     * 
     */
    public function solarFactory()
    {
        // bring in the config and get the adapter class.
        $config = $this->_config;
        $class = $config['adapter'];
        unset($config['adapter']);
        
        return Solar::factory($class, $config);
    }
}
