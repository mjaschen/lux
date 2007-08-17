<?php
/**
 *
 * Helper to build lists of named action links. Useful for layout nav elements.
 *
 * @category Lux
 *
 * @package Lux_View
 *
 * @author Rodrigo Moraes <rodrigo.moraes@gmail.com>
 *
 * @author Paul M. Jones <pmjones@solarphp.com>
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 *
 * @version $Id$
 *
 */

/**
 *
 * Helper to build lists of named action links. Useful for layout nav elements.
 *
 * @category Lux
 *
 * @package Lux_View
 *
 */
class Lux_View_Helper_ListNav extends Lux_View_Helper_ListBase
{
    /**
     *
     * Default display options. Keys are...
     *
     * `list_type`
     * : (string) Default 'ul', optionally 'ol'.
     *
     * `list_attribs`
     * : (array) List attributes.
     *
     * `item_class`
     * : (string) Class for each unselected item.
     *
     * `active_class`
     * : (string) Class for the active item.
     *
     * `active_link`
     * : (bool) Should the active item have a link?
     *
     * `first_class`
     * : (string) Class for the first item.
     *
     * `last_class`
     * : (string) Class for the last item.
     *
     * @var array
     *
     */
    protected $_Lux_View_Helper_ListNav = array(
        'display' => array(
            'list_type'    => 'ul',
            'list_attribs' => null,
            'item_class'   => null,
            'active_class' => 'active',
            'active_link'  => true,
            'first_class'  => 'first',
            'last_class'   => 'last',
        )
    );

    /**
     *
     * Display options.
     *
     * @var array
     *
     */
    protected $_display;

    /**
     *
     * Currently active item key.
     *
     * @var string
     *
     */
    protected $_active;

    /**
     *
     * Helper to build lists of named action links.
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
    public function listNav($items, $active = null, $display = null)
    {
        // Set the active item.
        $this->_active = $active;

        // Set display options.
        $this->_display = array_merge($this->_config['display'],
            (array) $display);

        // Done.
        return $this->_processList($items, $this->_display['list_type'],
            $this->_display['list_attribs']);
    }

    /**
     *
     * Processes list items, adding attributes and locale translations.
     *
     * @param string|int $key Item key.
     *
     * @param string|array $spec Item specs.
     *
     * @param int $iteration The iteration number for this item in the list.
     *
     * @param int $total Total number of items.
     *
     * @return string The processed list item.
     *
     */
    protected function _processItem($key, $spec, $iteration = null,
        $total = null)
    {
        // Start the class list.
        $class = array();

        // Add first / last class?
        if($iteration == 1 && $this->_display['first_class']) {
            // First item class.
            $class[] = $this->_display['first_class'];
        } elseif($iteration == $total && $this->_display['last_class']) {
            // Last item class.
            $class[] = $this->_display['last_class'];
        }

        // Is this the active item?
        $selected = $key === $this->_active;

        // Add selected / item class?
        if($selected && $this->_display['active_class']) {
            // Selected class.
            $class[] = $this->_display['active_class'];
        } elseif(!$selected && $this->_display['item_class']) {
            // Item class.
            $class[] = $this->_display['item_class'];
        }

        // Add attribs?
        if(!empty($class)) {
            $attribs = $this->_view->attribs(array('class' => $class));
        } else {
            $attribs = null;
        }

        // Should the selected item have a link?
        if($selected && !$this->_display['active_link']) {
            unset($spec['href']);
        }

        $text = $this->_processItemContent($spec);

        // Done!
        return "    <li$attribs>$text</li>";
    }
}