<?php
/**
 *
 * Adapter to load plugins from Solar::$config.
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
 * Adapter to load plugins from Solar::$config.
 *
 * @category Lux
 *
 * @package Lux_Event
 *
 */
class Lux_Event_Loader_Adapter_Config extends Lux_Event_Loader_Adapter
{
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
     * @todo Extract event lookup and implement a cache system.
     * @todo Define some exceptions and error handling.
     *
     */
    public function load($spec)
    {
        // Find plugin classes defined for this class and all its parents.
        $plugins = $this->_getPluginClasses($spec);

        if(empty($plugins)) {
            // No plugins defined.
            return;
        }

        // Iterate over the plugin list.
        foreach($plugins as $plugin) {
            try {
                $class_methods = get_class_methods($plugin);
            } catch(Exception $e) {
                // @todo log error.
            }

            foreach($class_methods as $method) {
                if(strpos($method, 'event') !== 0) {
                    // It's not an event method.
                    continue;
                }

                // Remove "event" from the method name.
                $event = substr($method, 5);
                // Make the event name start with lower case.
                $event[0] = strtolower($event[0]);

                Solar_Registry::get('event')->register($event, array($plugin, $method));
            }
        }
    }

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
    protected function _getPluginClasses($spec)
    {
        $classes = Solar::parents($spec, true);
        $plugins = array();

        foreach($classes as $class) {
            $config = (array) Solar::config($class, 'plugin');
            $plugins = array_merge($plugins, $config);
        }

        return $plugins;
    }
}
