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

/**
 *
 *
 *
 * @category Lux
 *
 * @package Lux_View_Helper
 *
 */
class Lux_View_Helper_Jquery_Accordion extends Lux_View_Helper_Jquery_Base
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
        $this->needsFile('ui.accordion.js');

        $this->needsStyle($this->_config['theme'] .'.accordion.css');
    }

    /**
     *
     * Includes the accordion initialization script.
     *
     * @param string $selector A suitable HTML selector for jQuery.
     *
     * @param array $config Accordion configuration options. Will be encoded
     * to a JSON string.
     *
     */
    public function accordion($selector, $config = null)
    {
        if($config) {
            // Encode configuration.
            $config = $this->json->encode($config);
        }

        // Add inline script.
        $script = '    $("' . $selector . '").accordion(' . $config . ');';
        $this->addInlineScript($script);
    }
}