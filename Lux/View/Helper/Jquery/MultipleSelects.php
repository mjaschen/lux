<?php
/**
 *
 * Allows multiple options to be selected and moved from a <select> element
 * to another <select> element.
 *
 * Uses the jQuery plugin jqMultiSelects:
 * [[jqMultiSelects | http://code.google.com/p/jqmultiselects/]]
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
class Lux_View_Helper_Jquery_MultipleSelects extends Solar_View_Helper
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

        $this->_view->jquery()->addScript('jquery.multiselects.js');
    }

    /**
     *
     * Interface method.
     *
     * @return Lux_View_Helper_Jquery_Accordion
     *
     */
    public function multipleSelects()
    {
        return $this;
    }

    /**
     *
     * Adds the inline script to activate the MultipleSelect element.
     *
     * @param string $from A jQuery selector for the source select element.
     *
     * @param string $to A jQuery selector for the destination select element.
     *
     * @param string $selector_to A jQuery selector for the triggering element
     * that moves options from one select to another when clicked.
     *
     * @return void
     *
     */
    public function set($from, $to, $trigger)
    {
        // Add inline script.
        $code = '$("' . $from . '")' .
                '.multiSelect("' . $to . '", "' . $trigger.'");';
        $this->addInlineScript($code);
    }
}