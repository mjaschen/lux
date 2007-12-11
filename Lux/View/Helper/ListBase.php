<?php
/**
 *
 * Base helper to build lists, iterating over the elements.
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
 * Base helper to build lists, iterating over the elements.
 *
 * @category Lux
 *
 * @package Lux_View
 *
 */
class Lux_View_Helper_ListBase extends Solar_View_Helper
{
    /**
     *
     * Builds a list.
     *
     * @param array $items List elements.
     *
     * @param string $type List type.
     *
     * @param array $attribs Additional attributes for the list.
     *
     * @return string The built list.
     *
     */
    public function listBase($items, $type = 'ul', $attribs = null)
    {
        // Done.
        return $this->_processList($items, $type, $attribs);
    }

    /**
     *
     * Builds a list.
     *
     * @param array $items List elements.
     *
     * @param string $type List type.
     *
     * @param array $attribs Additional attributes for the list.
     *
     * @return string The built list.
     *
     */
    public function _processList($items, $type, $attribs)
    {
        // Set the item list.
        $items = (array) $items;

        // Process list items.
        $i = 1;
        $total = count($items);
        $list = '';
        foreach($items as $key => $value) {
            $list .= $this->_processItem($key, $value, $i, $total);
            $i++;
        }

        // Set attribs.
        settype($attribs, 'array');
        $attribs = $this->_view->attribs($attribs);

        // Done!
        return "<$type$attribs>$list</$type>";
    }

    /**
     *
     * Processes a list item.
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
        // Process content.
        $content = $this->_processItemContent($spec);

        // Process attributes.
        if(array_key_exists('attribs', $spec)) {
            $attribs = (array) $spec['attribs'];
            $attribs = $this->_view->attribs($attribs);
        } else {
            $attribs = null;
        }

        // Done!
        return "<li$attribs>$content</li>";
    }

    /**
     *
     * Processes a list item content with built in action/anchor link creation.
     *
     * @param string|array $spec The item specification. If it is a string, will
     * return a locale translation. If it is an array, keys are...
     *
     * `text`
     * : (string) A locale translation key.
     *
     * `href`
     * : (string) An action href.
     *
     * `href_attribs`
     * : (string) Attributes for the action href.
     *
     * @return string The processed item content.
     *
     */
    protected function _processItemContent($spec)
    {
        if(is_string($spec)) {
            // Raw content: just return it.
            return $spec;
        }

        // Not a string? We need an array.
        $spec = (array) $spec;

        // Process content.
        if(!array_key_exists('text', $spec)) {
            // The key 'text' is required to build an action/anchor link.
            return '';
        }

        // Get the text translation.
        $text = $this->_getLocale($spec['text']);

        // Text content. Process href?
        if(array_key_exists('href', $spec)) {
            if(array_key_exists('href_attribs', $spec)) {
                $attribs = (array) $spec['href_attribs'];
                if(isset($attribs['title'])) {
                    // Get the title translation.
                    $attribs['title'] = $this->_getLocale($attribs['title']);
                }
            } else {
                $attribs = null;
            }

            // Try to guess when an anchor is needed.
            if( strpos($spec['href'], '://') ||
                strpos($spec['href'], '#') == 0) {
                // Full url.
                $res = $this->_view->anchor($spec['href'], $text, $attribs);
            } else {
                // Action uri.
                $res = $this->_view->action($spec['href'], $text, $attribs);
            }
        } else {
            // No href, just get a translation.
            $res = $text;
        }

        // Done!
        return $res;
    }

    /**
     *
     * Process text data, returning a translation with or without replacements.
     *
     * @param string|array Locale key or array with locale key and replacements.
     *
     * @return string The locale translation.
     *
     */
    protected function _getLocale($text)
    {
        if(is_array($text)) {
            // If it is an array, there are replacements to be done.
            return $this->_view->getText($text[0], 1, $text[1]);
        } else {
            return $this->_view->getText($text);
        }
    }
}
