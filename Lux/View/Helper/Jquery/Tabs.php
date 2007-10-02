<?php
/**
 *
 * Helper to build javascript tabs.
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
 * Helper to build javascript tabs.
 *
 * @category Lux
 *
 * @package Lux_View_Helper
 *
 */
class Lux_View_Helper_Jquery_Tabs extends Lux_View_Helper_Jquery_Base
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
        $this->needsFile('ui.tabs.js');
        $this->needsStyle($this->_config['theme'] . '.tabs.css');
    }

    /**
     *
     * Includes the tabs initialization script.
     *
     * @param string $selector A suitable HTML selector for jQuery.
     *
     * @param array $config Tabs configuration options. Will be encoded
     * to a JSON string.
     *
     */
    public function tabs($selector, $config = null)
    {
        if($config) {
            // Encode configuration.
            $config = $this->json->encode($config);
        }

        // Add inline script.
        $script = '    $("' . $selector . '").tabs(' . $config . ');';
        $this->addInlineScript($script);
    }
}