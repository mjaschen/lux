<?php
/**
 *
 * Handles event subscriptions and notifications.
 *
 * The pending, nested and global observer mechanisms were adapted from the
 * Pear package Event_Dispatcher, by Bertrand Mansion and Stephan Schmidt.
 *
 * @category Lux
 *
 * @package Lux_Event
 *
 * @author Travis Swicegood <development [at] domain51 [dot] com>
 *
 * @author Rodrigo Moraes <rodrigo.moraes@gmail.com>
 *
 * @author Bertrand Mansion <bmansion@mamasam.com>
 *
 * @author Stephan Schmidt <schst@php.net>
 *
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 *
 * @version $Id$
 *
 */

/**
 *
 * Handles event subscriptions and notifications.
 *
 * @category Lux
 *
 * @package Lux_Event
 *
 */
class Lux_Event_Handler extends Solar_Base
{
    /**
     *
     * User-provided configuration.
     *
     * Keys are ...
     *
     * `global`
     * : (string) Key used to register global observers.
     *
     * @var array
     *
     */
    protected $_Lux_Event_Handler = array(
        'global' => '_global',
    );

    /**
     *
     * Registered event subscriptions.
     *
     * @var array
     *
     */
    protected $_events = array();

    /**
     *
     * Registered observer objects.
     *
     * @var array
     *
     */
    protected $_observers = array();

    /**
     *
     * Pending notifications.
     *
     * @var array
     *
     */
    protected $_pending = array();

    /**
     *
     * Nested event handlers.
     *
     * @var array
     *
     */
    protected $_nested = array();

    // -------------------------------------------------------------------------
    //
    // Register observers.
    //
    // -------------------------------------------------------------------------

    /**
     *
     * Registers an event.
     *
     * @var string $event Event name.
     *
     * @var array $callback A valid callback.
     *
     * @var bool $static True if a static method call should be used for the
     * notification. False to force to create an object from a string and store
     * it in $this->_observers.
     *
     */
    public function set($event, $callback, $static = false)
    {
        // Create a unique identifier.
        $id = $this->_getCallbackId($callback, $static);

        $this->_events[$event][$id] = array(
            'callback' => $callback,
            'static'   => $static,
        );

        // Is there any pending notifications for this event?
        if(array_key_exists($event, $this->_pending)) {
            foreach($this->_pending[$event] as $key => $info) {
                // Send a notificatin disabling it to be added to pending.
                $this->notify($event, $info['subject'], $info['params'], false);
            }
        }
    }

    /**
     *
     * Checks if a callback is registered for a given event.
     *
     * @var string $event Event name.
     *
     * @var array $callback A valid callback.
     *
     * @return bool True if it is registered, false otherwise.
     *
     * @var bool $static True to check for a static method callback.
     *
     */
    public function exists($event, $callback, $static = false)
    {
        if(!array_key_exists($event, $this->_events)) {
            return false;
        }

        // Create a unique identifier.
        $id = $this->_getCallbackId($callback, $static);

        if(!array_key_exists($id, $this->_events[$event])) {
            return false;
        }

        return true;
    }

    // -------------------------------------------------------------------------
    //
    // Send notification.
    //
    // -------------------------------------------------------------------------

    /**
     *
     * Processes the list of observers registered for a given event. Also
     * processes globally registered observers, nested event handlers and
     * pending notifications.
     *
     * @var string $event Event name.
     *
     * @var object $subject The event caller (or "subject").
     *
     * @var array $params Optional parameters, commonly flags to be checked
     * by the subject after the event was executed.
     *
     * @param bool $pending If true, will send the notification even if the
     * observer registers for it later.
     *
     * @param bool $nested If true, will send the notification to nested event
     * handlers.
     *
     * @return void
     *
     */
    public function notify($event, $subject, &$params = null, $pending = false,
        $nested = true)
    {
        if($pending === true) {
            // Register the notification as pending.
            $this->_pending[$event][] = array(
                'subject' => $subject,
                'params'  => &$params,
            );
        }

        // Notify registered observers.
        if(array_key_exists($event, $this->_events)) {
            foreach($this->_events[$event] as $id => $info) {
                $this->_notify($info, $subject, $params);
            }
        }

        // Notify globally registered observers.
        if(array_key_exists($this->_config['global'], $this->_events)) {
            foreach($this->_events[$this->_config['global']] as $id => $info) {
                $this->_notify($info, $subject, $params);
            }
        }

        if ($nested === true) {
            // Notify nested event handlers.
            foreach($this->_nested as $name => $nested) {
                $nested->notify($event, $subject, $params, $pending);
            }
        }
    }

    /**
     *
     * Executes a registered event callback, notifying an observer about the
     * occurrence of an event. Avoids using call_user_func_array() if possible
     * for better performance.
     *
     * @var array $info Callback info array.
     *
     * @var object $subject The event caller (or "subject").
     *
     * @var array $params Optional parameters, commonly flags to be checked
     * by the subject after the event was executed.
     *
     * @return void
     *
     */
    protected function _notify($info, $subject, &$params)
    {
        $callback = $info['callback'];

        if(is_array($callback)) {
            if(!is_object($callback[0]) && $info['static'] === false) {
               // Create object on demand from a string: get the object
               // from $this->_observers, creating it if necessary.
               $callback[0] = $this->_getObserver($callback[0]);
            }

            // Check if it is a valid callback.
            $this->_checkCallback($callback);

            if(is_object($callback[0])) {
                $obj = $callback[0];
                $method = $callback[1];
                // Type 1: object method call.
                $obj->$method($subject, $params);
            } else {
                // Type 2: static method call.
                call_user_func_array($callback, array($subject, $params));
            }
        } else {
            // Check if it is a valid callback.
            $this->_checkCallback($callback);
            // Type 3: simple function call.
            $callback($subject, $params);
        }
    }

    // -------------------------------------------------------------------------
    //
    // Nested event handlers.
    //
    // -------------------------------------------------------------------------

    /**
     *
     * Adds a nested event handler.
     *
     * @param Lux_Event_Handler $event_handler The nested event handler.
     *
     * @param string $name A name to identify the event handler.
     *
     */
    public function addNested(Lux_Event_Handler $event_handler, $name)
    {
        $this->_nested[$name] = $event_handler;
    }

   /**
    *
    * Removes a nested event handler.
    *
    * @param string $name The event handler name.
    *
    */
    public function removeNested($name)
    {
        if(array_key_exists($name, $this->_nested)) {
            unset($this->_nested[$name]);
        }
    }

    // -------------------------------------------------------------------------
    //
    // Support methods.
    //
    // -------------------------------------------------------------------------

    /**
     *
     * Given a callback, creates a identifier to make it unique for an event.
     *
     * @param mixed $callback A PHP callback.
     *
     * @return string $id The callback identifier.
     *
     * @var bool $static True if it is a static method call.
     *
     */
    protected function _getCallbackId($callback, $static = false)
    {
        if(is_array($callback)) {
            if(is_object($callback[0])) {
                // Use the object hash plus the method.
                $id = spl_object_hash($callback[0]) . '->' . $callback[1];
            } else {
                $delim = $static ? '::' : '->';
                // Use the class name plus the method.
                $id = $callback[0] . $delim . $callback[1];
            }
        } else {
            // Simple function callback.
            $id = $callback;
        }

        return $id;
    }

    /**
     *
     * Checks if a callback is valid, and throws an excpetion if it is not.
     *
     * @param mixed $callback Callback to be checked.
     *
     */
    protected function _checkCallback($callback)
    {
        if(!is_callable($callback)) {
            $info = array();
            if(is_array($callback) && !empty($callback)) {
                if(is_object($callback[0])) {
                    $info['class'] = get_class($callback[0]);
                } else {
                    $info['class'] = $callback[0];
                }
                if(isset($callback[1])) {
                    $info['method'] = $callback[1];
                }
            } else {
                $info['callback'] = $callback;
            }

            throw $this->_exception('ERR_CALLBACK_NOT_CALLABLE', $info);
        }
    }

    /**
     *
     * Returns a observer object stored in $this->_observers. Create and store
     * it if necessary.
     *
     * @param string $class Observer class name.
     *
     * @return object The observer.
     *
     */
    protected function _getObserver($class)
    {
        if(!isset($this->_observers[$class])) {
            $this->_observers[$class] = Solar::factory($class);
        }

        return $this->_observers[$class];
    }
}