<?php
/**
 *
 * jQuery base helper.
 *
 * @category Lux
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
class Lux_View_Helper_Jquery extends Solar_View_Helper
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
     * be taken from here, and are also related to the theme being used.
     *
     * `theme`
     * : (string) jQuery theme name.
     *
     * @var array
     *
     */
    protected $_Lux_View_Helper_Jquery = array(
        'scripts' => 'Lux/scripts/jquery/',
        'styles'  => 'Lux/styles/jquery/themes/',
        'images'  => 'Lux/images/jquery/themes/',
        'theme'   => 'lux',
    );

    /**
     *
     * Current theme in use.
     *
     * @param string Theme name.
     *
     */
    protected $_theme;

    /**
     *
     * A JSON object, shared by jQuery helpers.
     *
     * @param Solar_Json
     *
     */
    protected $_json;

    /**
     *
     * Constructor.
     *
     * @param array $config User-provided configuration values.
     *
     */
    public function __construct($config = null)
    {
        parent::__construct($config);

        // jQuery file is always needed. Add it as a base script.
        $this->addScriptBase('jquery.js');

        $this->_theme = $this->_config['theme'];
    }

    /**
     *
     * Fluent interface.
     *
     * @return Lux_View_Helper_Jquery
     *
     */
    public function jquery()
    {
        return $this;
    }

    /**
     *
     * Adds a <style> tag as part of the "baseline" (foundation) styles.
     * Generally used by layouts, not views.
     *
     * @param string $href The file HREF for the style source.
     *
     * @param array $attribs Attributes for the tag.
     *
     * @return Lux_View_Helper_Jquery
     *
     */
    public function addStyleBase($href, $attribs = null)
    {
        $href = $this->_config['styles'] . $this->_theme . '/' . $href;
        $this->_view->head()->addStyleBase($href, $attribs);
        return $this;
    }

    /**
     *
     * Adds a <style> tag as part of the "additional" (override) styles.
     * Generally used by views, not layouts.  If the file has already been
     * added, it does not get added again.
     *
     * @param string $href The file HREF for the style source.
     *
     * @param array $attribs Attributes for the tag.
     *
     * @return Lux_View_Helper_Jquery
     *
     */
    public function addStyle($href, $attribs = null)
    {
        $href = $this->_config['styles'] . $this->_theme . '/' . $href;
        $this->_view->head()->addStyle($href, $attribs);
        return $this;
    }

    /**
     *
     * Adds a <script> tag as part of the "baseline" (foundation) scripts.
     * Generally used by layouts, not views.  If the file has already been
     * added, it does not get added again.
     *
     * @param string $src The file HREF for the script source.
     *
     * @param array $attribs Attributes for the tag.
     *
     * @return Lux_View_Helper_Jquery
     *
     */
    public function addScriptBase($src, $attribs = null)
    {
        $src = $this->_config['scripts'] . $src;
        $this->_view->head()->addScriptBase($src, $attribs);
        return $this;
    }

    /**
     *
     * Adds a <script> tag as part of the "additional" (override) scripts.
     * Generally used by views, not layouts.  If the file has already been
     * added, it does not get added again.
     *
     * @param string $src The file HREF for the script source.
     *
     * @param array $attribs Attributes for the tag.
     *
     * @return Lux_View_Helper_Jquery
     *
     */
    public function addScript($src, $attribs = null)
    {
        $src = $this->_config['scripts'] . $src;
        $this->_view->head()->addScript($src, $attribs);
        return $this;
    }

    /**
     *
     * Adds a <script> tag with inline code.
     *
     * @param string $code The inline code for the tag.
     *
     * @return Lux_View_Helper_Jquery
     *
     */
    public function addScriptInline($code)
    {
        $code = "$(function() {\n$code\n});";
        $this->_view->head()->addScriptInline($code);
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
    public function getImage($src)
    {
        $src = $this->_config['images'] . $this->_theme . '/' . $src;
        return $this->_view->publicHref($src);
    }

    /**
     *
     * Returns the name of the jQuery theme in use.
     *
     * Useful to set a class for HTML elements using the configured theme name.
     *
     * @return string Theme name.
     *
     */
    public function getTheme()
    {
        return $this->_theme;
    }

    /**
     *
     * Sets the theme used to get jQuery related styles and images.
     *
     * @params string Theme name.
     *
     * @return Lux_View_Helper_Jquery
     *
     */
    public function setTheme($theme)
    {
        $this->_theme = $theme;
        return $this;
    }

    /**
     *
     * Returns a jQuery helper object; creates it as needed.
     *
     * @param string $helper Name of jQuery helper class.
     *
     * @return object A jQuery helper object.
     *
     */
    protected function __call($method, $params)
    {
        // Add a prefix for the helper class.
        $class = 'Jquery_' . ucfirst($method);
        $helper = $this->_view->getHelper($class);
        return call_user_func_array(array($helper, $method), $params);
    }

    /**
     *
     * Returns a JSON object.
     *
     * @return Solar_Json
     *
     */
    public function json()
    {
        if(!$this->_json) {
            $this->_json = Solar::factory('Solar_Json');
        }

        return $this->_json;
    }
}