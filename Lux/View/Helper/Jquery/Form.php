<?php
/**
 *
 *
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
class Lux_View_Helper_Jquery_Form extends Solar_View_Helper
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
            ->addScript('jquery.form.js');
    }

    /**
     *
     * Interface method.
     *
     * @return Lux_View_Helper_Jquery_Form
     *
     */
    public function form()
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
    }
}