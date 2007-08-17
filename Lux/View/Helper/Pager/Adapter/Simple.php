<?php
/**
 *
 * Pager adapter for 'simple' style.
 *
 * @category Lux
 *
 * @package Lux_View
 *
 * @author Paul M. Jones <pmjones@solarphp.com>
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
 * Pager adapter for 'simple' style.
 *
 * @category Lux
 *
 * @package Lux_View
 *
 */
class Lux_View_Helper_Pager_Adapter_Simple extends Lux_View_Helper_Pager_Adapter
{
    /**
     *
     * User-provided configuration values. Keys are...
     *
     * `show_current`
     * : (bool) If false, doesn't include the current page number. Allows to
     *   make a really simple pager, showing just the previous/next links.
     *
     * @var array
     *
     */
    protected $_Lux_View_Helper_Pager_Adapter_Simple = array(
        'show_current' => true,
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
        // Add the previous page link.
        $this->_setPrevious();

        if($this->_config['show_current']) {
            // Add the current page link.
            $replace = array($this->_page, $this->_pages);
            $text = $this->_view->getText('PAGER_PAGE_X_OF_Y', 1, $replace);
            $this->_setCurrent('', $text);
        }

        // Add the next page link.
        $this->_setNext();
    }
}