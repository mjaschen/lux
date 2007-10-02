<?php
/**
 *
 * Attachs a calendar widget to a form field.
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
 * Attachs a calendar widget to a form field.
 *
 * @category Tipos
 *
 * @package Lux_View_Helper
 *
 */
class Lux_View_Helper_Jquery_Calendar extends Lux_View_Helper_Jquery_Base
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
        $this->needsFile('ui.calendar.js');
        $this->needsStyle('calendar/ui.calendar.css');
    }

    /**
     *
     * Adds the inline script to activate the calendar widget.
     *
     * @param string $selector A jQuery selector for the target form element.
     *
     * @param array $options Calendar configuration options.
     *
     * @return void
     *
     */
    public function calendar($selector, $config = null)
    {
        if(!$config) {
            // Set a default configuration.
            $config = array(
                'autoPopUp'       => 'both',
                'buttonImageOnly' => true,
                'buttonImage'     => $this->getImage('calendar/calendar.gif'),
                'buttonText'      => 'Calendar',
            );
        }

        // Encode configuration.
        $config = $this->json->encode($config);

        // Add inline script.
        $script = '    $("' . $selector . '").calendar(' . $config . ');';
        $this->addInlineScript($script);
    }
}