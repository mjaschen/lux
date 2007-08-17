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
class Lux_View_Helper_Pager_Adapter_Sliding extends Lux_View_Helper_Pager_Adapter
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
     * `expanded`
     * : (bool)
     *
     * @var array
     *
     */
    protected $_Lux_View_Helper_Pager_Adapter_Sliding = array(
        'delta'           => 3,
        'show_first_page' => false,
        'show_last_page'  => false,
        // special configs for this adapter
        'expanded'        => false,
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
        if ($this->_config['show_first_page'] &&
            $this->_pages > (2 * $this->_config['delta'] + 1)) {
            $this->_setFirst();
        }

        // Add the previous page link.
        $this->_setPrevious();

        // Add the item links.
        $this->_addItems();

        // Add the next page link.
        $this->_setNext();

        // Add the last page link.
        if ($this->_config['show_last_page'] &&
            $this->_pages > (2 * $this->_config['delta'] + 1)) {
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
        if($this->_pages > (2 * $this->_config['delta'] + 1)) {
            if ($this->_config['expanded']) {
                if(($this->_pages - $this->_config['delta']) <= $this->_page) {
                    $expansion_before = $this->_page - ($this->_pages - $this->_config['delta']);
                } else {
                    $expansion_before = 0;
                }
                for($i = (int) $this->_page - $this->_config['delta'] - $expansion_before;
                    $expansion_before; $expansion_before--, $i++) {
                    // Add an ordinary page link.
                    $i = (int) $i;
                    $this->_setItem($i);
                }
            }

            $expansion_after = 0;
            for($i = $this->_page - $this->_config['delta'];
                ($i <= $this->_page + $this->_config['delta'])
                && ($i <= $this->_pages); $i++) {
                if($i < 1) {
                    ++$expansion_after;
                    continue;
                }

                if($i == $this->_page) {
                    // Add the current page link.
                    $this->_setCurrent($i);
                } else {
                    // Add an ordinary page link.
                    $this->_setItem($i);
                }
            }

            if($this->_config['expanded'] && $expansion_after) {
                for($i = $this->_page + $this->_config['delta'] +1;
                    $expansion_after; $expansion_after--, $i++) {
                    $i = (int) $i;
                    // Add an ordinary page link.
                    $this->_setItem($i);
                }
            }

        } else {
            for($i = 1; $i <= $this->_pages; $i++) {
                if($i != $this->_page) {
                    // Add an ordinary page link.
                    $this->_setItem($i);
                } else {
                    // Add the current page link.
                    $this->_setCurrent($i);
                }
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

        if($page <= $this->_pages) {
            if ($this->_config['expanded']) {
                if($page <= $this->_config['delta']) {
                    $min = $this->_config['delta'] - $page + 1;
                } else {
                    $min = 0;
                }

                if($page >= ($this->_pages - $this->_config['delta'])) {
                    $max = $page - ($this->_pages - $this->_config['delta']);
                } else {
                    $max = 0;
                }
            } else {
                $min = $max = 0;
            }

            return array(
                max($page - $this->_config['delta'] - $max, 1),
                min($page + $this->_config['delta'] + $min, $this->_pages)
            );
        }

        return array(0, 0);
    }
}