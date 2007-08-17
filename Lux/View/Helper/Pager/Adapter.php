<?php
/**
 *
 * Base class for pager adapters.
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
 * Base class for pager adapters.
 *
 * @category Lux
 *
 * @package Lux_View
 *
 */
abstract class Lux_View_Helper_Pager_Adapter extends Solar_View_Helper
{
    /**
     *
     * User-provided configuration values. Keys are...
     *
     * `decorator`
     * : (string) Name of the helper used to build the pager list, located at
     * /Pager/Decorator
     *
     * `display`
     * : (array) Decorator config options.
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
    protected $_Lux_View_Helper_Pager_Adapter = array(
        'decorator' => 'pagesList',
        'display'   => array(),
        'locale'    => array(
            'first'    => 'PAGER_FIRST_PAGE',
            'previous' => 'PAGER_PREVIOUS_PAGE',
            'next'     => 'PAGER_NEXT_PAGE',
            'last'     => 'PAGER_LAST_PAGE',
        ),
    );

    /**
     *
     * The base href for the pager links.
     *
     * @var string
     *
     */
    protected $_href;

    /**
     *
     * Current page number.
     *
     * @var int
     *
     */
    protected $_page;

    /**
     *
     * Current page number.
     *
     * @var int
     *
     */
    protected $_paging;

    /**
     *
     * Number of items to be paginated.
     *
     * @var int
     *
     */
    protected $_count;

    /**
     *
     * Calculated number of pages.
     *
     * @var int
     *
     */
    protected $_pages;

    /**
     *
     * List of pager items to build the pager.
     *
     * @var array
     *
     */
    protected $_list;

    /**
     *
     * Fetches the decorated pager links.
     *
     * @param string $href The base href for the pager links.
     *
     * @param array $spec Pager specification. Keys are...
     *
     * `page`
     * : (int) Current page number.
     *
     * `paging`
     * : (int) How many items are displayed per page.
     *
     * `count`
     * : (int) Number of items to be paginated.
     *
     * `pages`
     * : (int) Number of pages. If 'count' is set (preferable), this value will
     *   be ignored.
     *
     * @return string The HTML output.
     *
     */
    public function fetch($href, $spec)
    {
        // Set href.
        $this->_href = rtrim($href, '/');

        $spec = (array) $spec;

        // Set page.
        if(array_key_exists('page', $spec)) {
            $this->_page = (int) $spec['page'];
        }

        // Set paging.
        if(array_key_exists('paging', $spec)) {
            $this->_paging = (int) $spec['paging'];
        }

        if(array_key_exists('count', $spec)) {
            // Set count.
            $this->_count = (int) $spec['count'];
            // Calculate the number of pages.
            if($this->_paging > 0) {
                $this->_pages = ceil($this->_count / $this->_paging);
            }
        } elseif(array_key_exists('pages', $spec)) {
            // Set pages.
            $this->_pages = (int) $spec['pages'];
            // We'll guess the approximate number of items here, so that's why
            // setting 'count' is preferable.
            $this->_count = $this->_pages * $this->_paging;
        }

        // Set the items list.
        $this->_setList();

        // Get the decorator.
        $name = $this->_config['decorator'];
        $helper = $this->_view->getHelper('Pager_Decorator_' . ucfirst($name));

        // Build and return the list.
        return $helper->$name($this->_list, $this->_config['display']);
    }

    /**
     *
     * Adds a "first page" item to $this->_list.
     *
     * @param string $text A key for a locale key defined in config or a locale
     * translation key.
     *
     * @todo title for links
     *
     */
    protected function _setFirst($text = 'first')
    {
       if($this->_page === 1) {
           return;
       }

       $this->_list[] = array(
           'type'    => 'first',
           'content' => $this->_getAction(1, $text)
       );
    }

    /**
     *
     * Adds a "previous page" item to $this->_list.
     *
     * @param string $text A key for a locale key defined in config or a locale
     * translation key.
     *
     * @todo title for links
     *
     */
    protected function _setPrevious($text = 'previous')
    {
       if($this->_page === 1) {
           return;
       }

       $this->_list[] = array(
           'type'    => 'previous',
           'content' => $this->_getAction($this->_page - 1, $text)
       );
    }

    /**
     *
     * Adds an ordinary page item to $this->_list.
     *
     * @param int $page Page number.
     *
     * @param string $text A key for a locale key defined in config or a locale
     * translation key.
     *
     * @todo title for links.
     *
     */
    protected function _setItem($page, $text = '')
    {
        if(empty($text)) {
            $text = $page;
        }

        $this->_list[] = array(
            'type'    => 'item',
            'content' => $this->_getAction($page, $text)
        );
    }

    /**
     *
     * Adds a "current page" item to $this->_list (no link).
     *
     * @param int $page Page number.
     *
     * @param string $text A key for a locale key defined in config or a locale
     * translation key.
     *
     */
    protected function _setCurrent($page = '', $text = '')
    {
        if(empty($page)) {
            $page = $this->_page;
        }

        if(empty($text)) {
            $text = $page;
        }

        $this->_list[] = array(
            'type'    => 'current',
            'content' => $text
        );
    }

    /**
     *
     * Adds a "next page" item to $this->_list.
     *
     * @param string $text A key for a locale key defined in config or a locale
     * translation key.
     *
     * @todo title for links
     *
     */
    protected function _setNext($text = 'next')
    {
       if (($this->_page + 1) > $this->_pages) {
           return;
       }

       $this->_list[] = array(
           'type'    => 'next',
           'content' => $this->_getAction($this->_page + 1, $text)
       );
    }

    /**
     *
     * Adds a "last page" item to $this->_list.
     *
     * @param string $text A key for a locale key defined in config or a locale
     * translation key.
     *
     * @todo title for links
     *
     */
    protected function _setLast($text = 'last')
    {
       if($this->_page === $this->_pages) {
           return;
       }

       $this->_list[] = array(
           'type'    => 'last',
           'content' => $this->_getAction($this->_pages, $text)
       );
    }

    /**
     *
     * Builds an action specification. Adapters can extend this to support
     * different url parameters.
     *
     * @param int $page Page number.
     *
     * @param string $text A key for a locale key defined in config or a locale
     * translation key.
     *
     * @return string The action spec.
     *
     * @todo title for links
     *
     */
    protected function _getAction($page, $text)
    {
        $query_string = $this->_view->getText('PAGER_PAGE_QUERY');

        if(array_key_exists($text, $this->_config['locale'])) {
            // Get the locale key from the config.
            $text = $this->_config['locale'][$text];
        }

        return $this->_view->action("{$this->_href}?$query_string=$page", $text);
    }
}