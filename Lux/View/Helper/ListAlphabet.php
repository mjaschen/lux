<?php
/**
 *
 * Helper to build an alphabet with action links.
 *
 * @category Lux
 *
 * @package Lux_View
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
 * Helper to build an alphabet with action links.
 *
 * @category Lux
 *
 * @package Lux_View
 *
 */
class Lux_View_Helper_ListAlphabet extends Solar_View_Helper
{
    /**
     *
     * Builds an alphabet with action links.
     *
     * @param string $uri Uri for items action links, formatted using sprintf().
     *
     * @param string $active The active/current/selected item key.
     *
     * @param array $display Additional display options to build the list.
     *
     * @param array $pre_list Optional items for the beginning of the list.
     *
     * @param array $post_list Optional items for the end of the list.
     *
     * @return string The list of action items.
     *
     */
    public function listAlphabet($uri, $active = null, $display = null,
        $pre_list = null, $post_list = null)
    {
        // Start the list with $pre_list items?
        if($pre_list) {
            $items = (array) $pre_list;
        } else {
            $items = array();
        }

        // Build the alphabet.
        $list = range('a', 'z');

        // Set action info for each letter.
        foreach($list as $letter) {
            $items[$letter] = array(
                'href' => sprintf($uri, $letter),
                'text' => $letter,
            );
        }

        // Add $post_list items to the end of the list?
        if($post_list) {
            $items = array_merge($items, (array) $post_list);
        }

        // Done.
        return $this->_view->listNav($items, $active, $display);
    }
}
