<?php
/**
 *
 * Pager adapter for 'jumping' style.
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
 * Pager adapter for 'jumping' style.
 *
 * @category Lux
 *
 * @package Lux_View
 *
 */
class Lux_View_Helper_Pager_Adapter_Jumping extends Lux_View_Helper_Pager_Adapter
{
    /**
     *
     * User-provided configuration values. Keys are...
     *
     * `delta`
     * : (int) Number of page links for each window.
     *
     * `show_first_page`
     * : (bool) Show a link to the first page?
     *
     * `show_last_page`
     * : (bool) Show a link to the last page?
     *
     * `last_pages`
     * : (int) Number of last pages to display in the list.
     *
     * `first_pages`
     * : (int) Number of first pages to display in the list.
     *
     * @var array
     *
     */
    protected $_Lux_View_Helper_Pager_Adapter_Jumping = array(
        'delta'           => 3,
        'show_first_page' => false,
        'show_last_page'  => false,
        // special configs for this adapter
        'last_pages'      => 0,
        'first_pages'     => 0,
    );

    /**
     *
     * Builds an array of pager links data to be decorated.
     *
     * @return void
     *
     */
    public function _setList()
    {
        // Add the first page link.
        if($this->_config['show_first_page'] && $this->_page != 1) {
            $this->_setFirst();
        }

        // Add the previous page link.
        $this->_setPrevious();

        // Add the item links.
        $this->_addItems();

        // Add the next page link.
        $this->_setNext();

        // Add the last page link.
        if($this->_config['show_last_page'] && $this->_page != $this->_pages) {
            $this->_setLast();
        }
    }

    /**
     *
     * Adds ordinary pager items to the pager list following a page range.
     *
     * This method comes from Pear::Pager / Class Pager_Jumping.
     *
     * @link http://pear.php.net/package/Pager
     *
     * @author Lorenzo Alberton <l dot alberton at quipo dot it>
     *
     * @author Richard Heyes <richard@phpguru.org>
     *
     * @copyright 2003-2006 Lorenzo Alberton, Richard Heyes
     *
     * @license http://opensource.org/licenses/bsd-license.php BSD
     *
     * @return void
     *
     */
    protected function _addItems()
    {
        $limits = $this->_getPageRange($this->_page);

        // @todo add some of the first pages to the list

        // Add the page range to the list.
        $min = min($limits[1], $this->_pages);
        for($i = $limits[0]; $i <= $min; $i++) {
            if ($i != $this->_page) {
                // Add an ordinary page link.
                $this->_setItem($i);
            } else {
                // Add the current page link.
                $this->_setCurrent($i);
            }
        }

        // Add some of the last pages to the list.
        for($j = $this->_config['last_pages'] - 1; $j >= 0; $j--) {
            if($i < $this->_pages - $j) {
                // Only show the separator for the first in the list.
                $last_separator = isset($last_separator_displayed) ? '' : '... ';

                // Add an ordinary page link.
                $page = $this->_pages - $j;
                $text = $last_separator . $page;
                $this->_setItem($page, $text);

                // The separator is only used once.
                $last_separator_displayed = true;
            }
        }
    }

    /**
     *
     * Given a page number, returns the limits of the range of pages displayed.
     * While getOffsetByPageId() returns the offset of the data within the
     * current page, this method returns the offsets of the page numbers
     * interval. E.g., if you have page=3 and delta=10, it will return (1, 10).
     * Page 8 would give you (1, 10) as well, because 1 <= 8 <= 10.
     * Page of 11 would give you (11, 20).
     * If the method is called without parameter, page is set to $this->_page.
     *
     * This method comes from Pear::Pager / Class Pager_Jumping.
     *
     * @link http://pear.php.net/package/Pager
     *
     * @author Lorenzo Alberton <l dot alberton at quipo dot it>
     *
     * @author Richard Heyes <richard@phpguru.org>
     *
     * @copyright 2003-2006 Lorenzo Alberton, Richard Heyes
     *
     * @license http://opensource.org/licenses/bsd-license.php BSD
     *
     * @param int Page number to get offsets for.
     *
     * @return array First and last offsets.
     *
     */
    protected function _getPageRange($page = null)
    {
        if($page) {
            $page = (int) $page;
        } else {
            $page = $this->_page;
        }

        if ($page <= $this->_pages) {
            $delta = $this->_config['delta'];
            // I'm sure I'm missing something here, but this formula works
            // so I'm using it until I find something simpler.
            $start = ((($page + (($delta - ($page % $delta))) % $delta) / $delta) - 1) * $delta + 1;
            return array(
                max($start, 1),
                min($start + $delta - 1, $this->_pages)
            );
        } else {
            return array(0, 0);
        }
    }
}