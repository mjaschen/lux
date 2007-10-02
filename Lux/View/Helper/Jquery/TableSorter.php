<?php
/**
 *
 * Javascript helper to turn a standard HTML table with THEAD and TBODY tags
 * into a sortable table without page refreshes.
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
 * Javascript helper to turn a standard HTML table with THEAD and TBODY tags
 * into a sortable table without page refreshes.
 *
 * @category Lux
 *
 * @package Lux_View_Helper
 *
 */
class Lux_View_Helper_Jquery_TableSorter extends Lux_View_Helper_Jquery_Base
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
        $this->needsFile('ui.tablesorter.js');
        $this->needsStyle($this->_config['theme'] . '.tablesorter.css');
    }

    /**
     *
     * Includes the table sorter initialization script for one or more tables.
     *
     * @param string $selector A suitable HTML selector for jQuery.
     *
     * @param array $config TableSorter configuration options. Will be encoded
     * to a JSON string.
     *
     * @return Lux_View_Helper_Jquery_TableSorter
     *
     */
    public function tableSorter($selector, $config = null)
    {
        if($config) {
            // Encode configuration.
            $config = $this->json->encode($config);
        }

        // Add inline script.
        $script = '    $("' . $selector . '").tablesorter(' . $config . ');';
        $this->addInlineScript($script);

        return $this;
    }
}