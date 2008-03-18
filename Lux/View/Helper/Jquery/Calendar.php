<?php
/**
 *
 * jQuery calendar helper.
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
class Lux_View_Helper_Jquery_Calendar extends Solar_View_Helper
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
            ->addScript('ui.calendar.js')
            // Styles
            ->addStyle('calendar.css');
    }

    /**
     *
     * Interface method.
     *
     * @return Lux_View_Helper_Jquery_Calendar
     *
     */
    public function calendar()
    {
        return $this;
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
    public function set($selector, $config = null)
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
        $config = $this->_view->json()->encode($config);

        // Add inline script.
        $code = '$("' . $selector . '").calendar(' . $config . ');';
        $this->_view->jquery()->addScriptInline($code);
    }
}