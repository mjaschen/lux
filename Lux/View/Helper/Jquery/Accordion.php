<?php
/**
 *
 * jQuery accordion helper.
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
class Lux_View_Helper_Jquery_Accordion extends Solar_View_Helper
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
            ->addScript('jquery.dimensions.js')
            ->addScript('ui.accordion.js')
            // Styles
            ->addStyle('accordion.css');
    }

    /**
     *
     * Interface method.
     *
     * @return Lux_View_Helper_Jquery_Accordion
     *
     */
    public function accordion()
    {
        return $this;
    }

    /**
     *
     * Includes the accordion initialization script.
     *
     * @param string $selector A suitable HTML selector for jQuery.
     *
     * @param array $config Accordion configuration options. Will be encoded
     * to a JSON string.
     *
     */
    public function set($selector, $config = null)
    {
        if($config) {
            // Encode configuration.
            $config = $this->_view->jquery()->json()->encode($config);
        }

        // Add inline script.
        $code = '$("' . $selector . '").accordion(' . $config . ');';
        $this->_view->jquery()->addScriptInline($code);
    }
}