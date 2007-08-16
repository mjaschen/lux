<?php
/**
 *
 * Abstract base class for plugin load adapters.
 *
 * @category Lux
 *
 * @package Lux_Event
 *
 * @author Rodrigo Moraes <rodrigo.moraes@gmail.com>
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 *
 * @version $Id$
 *
 */

/**
 *
 * Abstract base class for plugin load adapters.
 *
 * @category Lux
 *
 * @package Lux_Event
 *
 */
abstract class Lux_Event_Loader_Adapter extends Solar_Base
{
    /**
     *
     * Constructor.
     *
     * @param array $config User-provided configuration values.
     *
     */
    public function __construct($config = null)
    {
        // Do the parent construction.
        parent::__construct($config);

        // Register the plugin event handler, if not yet registered.
        if(!Solar::isRegistered('event')) {
            Solar::register('event', Solar::factory('Lux_Event_Handler'));
        }
    }

    /**
     *
     * Loads plugins for the given class/object, if defined. It will
     * look for plugins defined for the class/object and all its parents.
     * Then it will look for event methods on the plugin classes and
     * register the events in the event manager object.
     *
     * @param string|object $spec The class or object to find plugins for. It
     * will look on the class/object and all its parent classes to get plugins
     * defined for them.
     *
     */
    abstract public function load($spec);

    /**
     *
     * Fetches plugins defined for the class/object and all its parents.
     *
     * @param string|object $spec The class or object to find plugins for. It
     * will look on the class/object and all its parent classes to get plugins
     * defined for them.
     *
     * @return array A list of plugin classes.
     *
     */
    abstract protected function _getPluginClasses($spec);
}