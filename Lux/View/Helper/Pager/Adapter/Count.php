<?php
/**
 *
 * Pager adapter for 'count' style (GMail like).
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
 * Pager adapter for 'count' style (GMail like).
 *
 * @category Lux
 *
 * @package Lux_View
 *
 */
class Lux_View_Helper_Pager_Adapter_Count extends Lux_View_Helper_Pager_Adapter
{
    /**
     *
     * User-provided configuration values. Keys are...
     *
     * `locale`
     * : (array) Locale definitions for the different types of items. Keys are...
     *
     * `first`
     * : (string) Locale key for 'first' page.
     *
     * `previous`
     * : (string) Locale key for 'previous' page.
     *
     * `next`
     * : (string) Locale key for 'next' page.
     *
     * `last`
     * : (string) Locale key for 'last' page.
     *
     * @var array
     *
     */
    protected $_Lux_View_Helper_Pager_Adapter_Count = array(
        'locale' => array(
            'first'     => 'PAGER_NEWEST',
            'previous'  => 'PAGER_NEWER',
            'next'      => 'PAGER_OLDER',
            'last'      => 'PAGER_OLDEST',
        ),
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
        if($this->_pages > 2 && $this->_page > 2) {
            // Add the first page link.
            $this->_setFirst();
        }

        // Add the previous page link.
        $this->_setPrevious();

        if($this->_count <= $this->_paging) {
            // Add the current page link.
            $replace = array($this->_page);
            $text = $this->_view->getText('PAGER_PAGE_X', 1, $replace);
            $this->_setCurrent('', $text);
        } else {
            // Add the current page link.
            $end = $this->_page * $this->_paging;
            $start = $end - $this->_paging + 1;
            $replace = array($start, $end, $this->_count);
            $text = $this->_view->getText('PAGER_ITEMS_X-Y_OF_Z', 1, $replace);
            $this->_setCurrent('', $text);
        }

        // Add the next page link.
        $this->_setNext();

        if($this->_pages > 2 && $this->_page < ($this->_pages - 1)) {
            // Add the last page link.
            $this->_setLast();
        }
    }
}