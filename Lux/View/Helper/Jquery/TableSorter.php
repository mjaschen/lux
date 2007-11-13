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
class Lux_View_Helper_Jquery_TableSorter extends Solar_View_Helper
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
            ->addScript('ui.tablesorter.js')
            // Styles
            ->addStyle('tablesorter.css');
    }

    /**
     *
     * Interface method.
     *
     * @return Lux_View_Helper_Jquery_TableSorter
     *
     */
    public function tableSorter()
    {
        return $this;
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
     */
    public function set($selector, $config = null)
    {
        if($config) {
            // Encode configuration.
            $config = $this->_view->jquery()->json()->encode($config);
        }

        // Add inline script.
        $code = '$("' . $selector . '").tablesorter(' . $config . ');';
        $this->_view->jquery()->addScriptInline($code);
    }
}