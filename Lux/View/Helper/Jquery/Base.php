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
 * @author Rodrigo Moraes <rodrigo.moraes@gmail.com>
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
class Lux_View_Helper_Jquery_Base extends Solar_View_Helper
{
    /**
     *
     * User-provided configuration values. Keys are...
     *
     * `scripts`
     * : (string) Public path to jQuery scripts.
     *
     * `styles`
     * : (string) Public path to jQuery styles. This defines the jQuery theme.
     *
     * `images`
     * : (string) Public path to jQuery images. Images needed by scripts will
     * be taken from here.
     *
     * @var array
     *
     */
    protected $_Lux_View_Helper_Jquery_Base = array(
        'scripts' => 'scripts/jquery/',
        'styles'  => 'styles/jquery/themes/flora/',
        'images'  => 'images/jquery/',
    );

    /**
     *
     * Method interface.
     *
     * @return Lux_View_Helper_Jquery_Base
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

    /**
     *
     * Add the specified JavaScript file to the Helper_Js file list
     * if it's not already present.
     *
     * @param string $file Name of .js file needed by Helper class
     *
     * @return Child JsLibrary object
     *
     */
    public function needsFile($file = null)
    {
        // Add configured path
        $file = $this->_config['scripts'] . $file;

        $this->_view->js()->addFile($file);
        return $this;
    }

    /**
     *
     * Add the specified JavaScript file to the Helper_Js file list
     * if it's not already present.
     *
     * @param string $file Name of .js file needed by Helper class
     *
     * @return Child JsLibrary object
     *
     */
    public function needsStyle($file = null)
    {
        // Add configured path
        $file = $this->_config['styles'] . $file;

        $this->_view->js()->addStyle($file);
        return $this;
    }

    /**
     *
     * Returns a public href for an image used by a Jquery helper.
     *
     * @param string $file Name of image file needed by the helper class.
     *
     * @return string Public image path.
     *
     */
    public function getImage($file = null)
    {
        return $this->_view->publicHref($this->_config['images'] . $file);
    }

    /**
     *
     * Magic getter.
     *
     */
    public function __get($key)
    {
        if($key == 'json') {
            return $this->_view->js()->json();
        } else {
            throw $this->_exception(
                'ERR_PROPERTY_NOT_DEFINED',
                array('property' => "\$$key")
            );
        }
    }
}