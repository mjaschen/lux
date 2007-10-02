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

/**
 *
 * Dialog widget.
 *
 * @category Lux
 *
 * @package Lux_View_Helper
 *
 */
class Lux_View_Helper_Jquery_Dialog extends Lux_View_Helper_Jquery_Base
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
        $this->needsFile('jquery.dimensions.js');
        $this->needsFile('ui.dialog.js');
        $this->needsFile('ui.resizable.js');
        $this->needsFile('ui.mouse.js');
        $this->needsFile('ui.draggable.js');

        $this->needsStyle('dialog/ui.dialog.css');
        $this->needsStyle('flora/flora.all.css');
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
    public function init($selector, $config = null)
    {
        if($config) {
            // Encode configuration.
            $config = $this->json->encode($config);
        } else {
            $config = '';
        }

        // Add inline script.
        $script = '    $("' . $selector . '").dialog(' . $config . ');';
        $this->addInlineScript($script);
    }
}