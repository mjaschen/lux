<?php
/**
 *
 * Adds a maxLenght to elements - usually textareas.
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
 * Adds a maxLenght to elements - usually textareas.
 *
 * @category Tipos
 *
 * @package Lux_View_Helper
 *
 */
class Lux_View_Helper_Jquery_TextLimiter extends
    Lux_View_Helper_Jquery_Base
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
     * Adds the inline script to activate the maxLenght property in a textarea.
     *
     * @param string $selector A jQuery selector for the target form element.
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