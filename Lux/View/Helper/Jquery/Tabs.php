<?php
/**
 *
 * Helper to build javascript tabs.
 *
 * @category Lux
 *
 * @package Lux_View_Helper
 *
 * @author Rodrigo Moraes <rodrigo.moraes@gmail.com>
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 *
 * @version $Id$
 *
 */
class Lux_View_Helper_Jquery_Tabs extends Solar_View_Helper
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

        $this->_view->jquery()
            // Scripts
            ->addScript('ui.tabs.js')
            // Styles
            ->addStyle('tabs.css');
    }

    /**
     *
     * Interface method.
     *
     * @return Lux_View_Helper_Jquery_Tabs
     *
     */
    public function tabs()
    {
        return $this;
    }

    /**
     *
     * Sets the initialization script.
     *
     * @param string $selector A suitable HTML selector for jQuery.
     *
     * @param array $config Tabs configuration options. Will be encoded
     * to a JSON string.
     *
     * @param array $deQuote Array of keys whose values should **not** be
     * quoted in the JSON encoded string.
     *
     */
    public function set($selector, $config = null, $deQuote = array())
    {
        if($config) {
            // Encode configuration.
            $config = $this->_view->json()->encode($config, $deQuote);
        }

        // Add inline script.
        $code = '$("' . $selector . '").tabs(' . $config . ');';
        $this->_view->jquery()->addScriptInline($code);
    }
}