<?php
/**
 *
 * jQuery base helper.
 *
 * @category Tipos
 *
 * @package Lux_View_Helper
 *
 * @author Clay Loveless <clay@killersoft.com>
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
 * jQuery base helper.
 *
 * @category Tipos
 *
 * @package Lux_View_Helper
 *
 */
class Lux_View_Helper_Jquery extends Lux_View_Helper_Js
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
        parent::__construct($config);

        // jQuery file is always needed. Use the library base to add it.
        $this->base()->needsFile('jquery.js');

        // Subscribe this to the 'FetchInlineScript' event.
        $callback = array($this, 'eventFetchInlineScript');
        Solar::registry('event')->register('fetchInlineScript', $callback);
    }

    /**
     *
     * Fluent interface.
     *
     * @return Lux_View_Helper_Jquery
     *
     */
    public function jquery()
    {
        return $this;
    }

    /**
     *
     * Returns a jQuery helper object; creates it as needed.
     *
     * @param string $helper Name of jQuery helper class
     *
     * @return object A new standalone helper object.
     *
     */
    protected function __call($method, $params)
    {
        // Because Solar_View_Helpers typically are *not* in sub-dirs
        $class = 'Jquery_' . ucfirst($method);
        $helper = $this->_view->getHelper($class);
        return call_user_func_array(array($helper, $method), $params);
    }

    /**
     *
     * Inline script event.
     *
     */
    public function eventFetchInlineScript($helper, &$params)
    {
        // Gather all registered scripts for output.
        if (!empty($this->scripts)) {
            // Add a jQuery wrapper.
            array_unshift($this->scripts, '$(document).ready(function() {');
            $this->scripts[] = '});';

            // Prepare the scripts.
            $scripts = implode("\n", $this->scripts);
            $scripts = trim($scripts);

            // Add the scripts to the result.
            $params['scripts'] .= $this->_view->inlineScript($scripts);
        }
    }
}