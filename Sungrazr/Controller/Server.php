<?php
/**
 *
 * Factory class for server adapters.
 *
 * Heavily modified and refactored from Zend_Server and related classes.
 *
 * Developed for, and then donated by, Mashery.com <http://mashery.com>.
 *
 * @category Tipos
 *
 * @package Sungrazr_Controller_Server
 *
 * @author Clay Loveless <clay@killersoft.com>
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 *
 * @version SVN: $Id$
 *
 */

/**
 *
 * Factory class for server adapters.
 *
 * @category Tipos
 *
 * @package Sungrazr_Controller_Server
 *
 */
class Sungrazr_Controller_Server extends Solar_Base {

    /**
     *
     * User-provided configuration.
     *
     * Keys are ...
     *
     * `adapter`
     * : (string) The adapter class for the factory, default
     * 'Sungrazr_Controller_Server_Adapter_Rest'.
     *
     * @var array
     *
     */
    protected $_Sungrazr_Controller_Server = array(
        'adapter' => 'Sungrazr_Controller_Server_Adapter_Rest',
    );

    /**
     *
     * Factory method to create server adapter objects.
     *
     * @return Sungrazr_Controller_Server_Adapter
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
