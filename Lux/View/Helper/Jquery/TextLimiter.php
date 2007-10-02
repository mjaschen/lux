<?php
/**
 *
 * Activate the maxLenght property in input fields, limiting the number of
 * characters they accept.
 *
 * Useful to limit text that can be inserted in a textarea.
 *
 * @category Tipos
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

/**
 *
 * Activate the maxLenght property in input fields, limiting the number of
 * characters they accept.
 *
 * @category Tipos
 *
 * @package Lux_View_Helper
 *
 */
class Lux_View_Helper_Jquery_TextLimiter extends Lux_View_Helper_Jquery_Base
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

        // Add scripts and CSS files.
        $this->needsFile('jquery.textLimiter.js');
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
    public function textLimiter($selector)
    {
        // Add inline script.
        $script = '    $("' . $selector . '").textLimiter();';
        $this->addInlineScript($script);
    }
}