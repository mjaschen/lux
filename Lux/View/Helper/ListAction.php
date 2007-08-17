<?php
/**
 *
 * Helper for simple lists of action links.
 * This is a handy wrapper to build lists passing an array of 'href' => 'text'.
 * It uses Lux_View_Helper_ListNav to build the list.
 *
 * @category Lux
 *
 * @package Lux_View
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
 * Helper for simple lists of action links.
 * This is a handy wrapper to build lists passing an array of 'href' => 'text'.
 * It uses Lux_View_Helper_ListNav to build the list.
 *
 * @category Lux
 *
 * @package Lux_View
 *
 */
class Lux_View_Helper_ListAction extends Lux_View_Helper_ListNav
{
    /**
     *
     * Helper to build simple lists of action links.
     *
     * @param array $items List of items using the format 'href' => 'text'.
     *
     * @param string $active The active/current/selected item href.
     *
     * @param array $display Additional display options to build the list.
     *
     * @return string The list of action items.
     *
     */
    public function listAction($items, $active = null, $display = null)
    {
        // Compatibility with previous 'list_class' key.
        if( array_key_exists('list_class', (array) $display) &&
            $display['list_class'] != null) {
            if(array_key_exists('list_attribs', $display)) {
                settype($display['list_attribs'], 'array');
            } else {
                $display['list_attribs'] = array();
            }

            // Add the list class to the attributes.
            $display['list_attribs']['class'] = $display['list_class'];
        }

        // Convert $href => $text to a named format.
        foreach($items as $href => $text) {
            $items[$href] = array(
                'href' => $href,
                'text' => $text,
            );
        }

        // Done.
        return $this->listNav($items, $active, $display);
    }
}