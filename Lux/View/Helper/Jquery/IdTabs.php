<?php
/**
 *
 * Helper to build navigation tabs using javascript.
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
 * Helper to build navigation tabs using javascript.
 *
 * @category Tipos
 *
 * @package Lux_View_Helper
 *
 */
class Lux_View_Helper_Jquery_IdTabs extends Lux_View_Helper_Jquery_Base
{
    /**
     *
     * Current ID for tabs, incremented each time a new tab is added.
     *
     * @var int
     *
     */
    protected $_id = 0;

    /**
     *
     * List of or tab (or navigation) items.
     *
     * @var array
     *
     */
    protected $_items;

    /**
     *
     * List of template names to fetch the contents for each tab.
     *
     * @var array
     *
     */
    protected $_templates;

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
        $this->needsFile('idTabs.js');
    }

    /**
     *
     * Interface method.
     *
     * @return Lux_View_Helper_Jquery_IdTabs
     *
     */
    public function idTabs()
    {
        return $this;
    }

    /**
     *
     * Registers items and templates for tabs.
     *
     * @param array $list
     *
     * @return Lux_View_Helper_Jquery_IdTabs
     *
     */
    public function register($list)
    {
        $this->_items = array();
        $this->_templates = array();

        foreach($list as $locale_code => $template) {
            $tab_id = 'idTab_' . $this->_id;

            $this->_items[$tab_id] = array(
                'text' => $locale_code,
                'href' => '#' . $tab_id,
            );

            $this->_templates[$tab_id] = $template;
            $this->_id++;
        }

        return $this;
    }

    /**
     *
     * Returns a menu for JsTabs.
     *
     * @param array $items List of items specifications.
     *
     * @param string $active The active/current/selected item key.
     *
     * @param array $display Additional display options to build the list.
     *
     * @return string The list of action items.
     *
     */
    public function getMenu($items = array(), $active = null, $display = null)
    {
        $items = array_merge((array) $this->_items, $items);

        $out = '<div class="idTabs">';
        $out .= $this->_view->listNav($items, $active, $display);
        $out .= '</div>';

        return $out;
    }

    /**
     *
     * Returns the fetched templates for each table, if set.
     *
     * @return string Fetched templates.
     *
     */
    public function getContents()
    {
        $out = '';

        foreach((array) $this->_templates as $tab_id => $template) {
            if(!$template) {
                continue;
            }

            // Fetch the view content.
            $out .= '<a name ="' . $tab_id . '" />';
            $out .= '<div id="' . $tab_id . '">';
            $out .= $this->_view->fetch($template);
            $out .= '</div>';
        }

        return $out;
    }
}