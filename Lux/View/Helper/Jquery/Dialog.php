<?php
/**
 *
 * Dialog widget.
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
class Lux_View_Helper_Jquery_Dialog extends Solar_View_Helper
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
            ->addScript('ui.dialog.js')
            ->addScript('ui.resizable.js')
            ->addScript('ui.mouse.js')
            ->addScript('ui.draggable.js')
            // Styles
            ->addStyle('dialog.css');
    }

    /**
     *
     * Interface method.
     *
     * @return Lux_View_Helper_Jquery_Dialog
     *
     */
    public function dialog()
    {
        return $this;
    }

    /**
     *
     * Defines a container as a dialog.
     *
     * @param string $selector A jQuery selector for the target container.
     *
     * @param array $options Dialog configuration options.
     *
     * @return void
     *
     */
    public function set($selector, $config = null)
    {
        if($config) {
            // Encode configuration.
            $config = $this->_view->json()->encode($config);
        } else {
            $config = '';
        }

        // Add inline script.
        $code = '$("' . $selector . '").dialog(' . $config . ');';
        $this->_view->jquery()->addScriptInline($code);
    }
}