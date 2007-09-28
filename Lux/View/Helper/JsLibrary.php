<?php
/**
 *
 * Base class for JavaScript library helpers.
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
abstract class Lux_View_Helper_JsLibrary extends Solar_View_Helper
{
    /**
     *
     * User-provided configuration values.
     *
     * @var array
     *
     */
    protected $_Lux_View_Helper_JsLibrary = array(
        'scripts' => null,
        'styles'  => null,
    );

    /**
     *
     * Method interface.
     *
     * @return Child JsLibrary object
     *
     */
    public function jsLibrary()
    {
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