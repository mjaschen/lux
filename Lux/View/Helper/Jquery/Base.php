<?php
/**
 *
 * Base class for jQuery helpers.
 *
 * @category Tipos
 *
 * @package Lux_View_Helper
 *
 * @author Clay Loveless <clay@killersoft.com>
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 *
 * @version $Id$
 *
 */

/**
 *
 * Base class for JavaScript library helpers.
 *
 * @category Tipos
 *
 * @package Lux_View_Helper
 *
 */
class Lux_View_Helper_Jquery_Base extends Lux_View_Helper_JsLibrary
{
    /**
     *
     * User-provided configuration values.
     *
     * @var array
     *
     */
    protected $_Lux_View_Helper_Jquery_Base = array(
        'scripts' => 'base/js/jquery/',
        'styles'  => 'base/css/jquery/',
        'images'  => 'base/img/jquery/',
    );

    /**
     *
     * Method interface.
     *
     * @return Child JsLibrary object
     *
     */
    public function base()
    {
        return $this;
    }

    /**
     *
     * Add the script defined in $src to the inline scripts array.
     *
     * @param string $src A snippet of JavaScript to be inserted in the head
     * of a document.
     *
     * @return Solar_View_Helper_Js
     */
    public function addInlineScript($src)
    {
        // Centralizes the scripts on the jQuery helper.
        $this->_view->jquery()->addInlineScript($src);
    }
}