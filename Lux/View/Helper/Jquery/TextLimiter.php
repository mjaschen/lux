<?php
/**
 *
 * Activate the maxLenght property in input fields, limiting the number of
 * characters they accept.
 *
 * Useful to limit text that can be inserted in a textarea.
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
class Lux_View_Helper_Jquery_TextLimiter extends Solar_View_Helper
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

        $this->_view->jquery()->addScript('jquery.textLimiter.js');
    }

    /**
     *
     * Interface method.
     *
     * @return Lux_View_Helper_Jquery_TextLimiter
     *
     */
    public function textLimiter()
    {
        return $this;
    }

    /**
     *
     * Adds the inline script to activate the maxLenght property for a input
     * field.
     *
     * @param string $selector A jQuery selector for the target input element.
     *
     * @return void
     *
     */
    public function set($selector)
    {
        // Add inline script.
        $code = '$("' . $selector . '").textLimiter();';
        $this->_view->jquery()->addScriptInline($code);
    }
}