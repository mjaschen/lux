<?php
/**
 *
 * Cleaned up version of Solar_View_Helper_Js, using Tipos event system to
 * fetch inline scripts.
 *
 * @category Tipos
 *
 * @package Lux_View_Helper_Js
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
 * Cleaned up version of Solar_View_Helper_Js, using Tipos event system to
 * fetch inline scripts.
 *
 * @category Tipos
 *
 * @package Lux_View_Helper_Js
 *
 * @author Clay Loveless <clay@killersoft.com>
 *
 */
class Lux_View_Helper_Js extends Lux_View_Helper_JsLibrary
{
    /**
     *
     * User-provided configuration values.
     *
     * @var array
     *
     */
    protected $_Lux_View_Helper_Js = array(
        'attribs' => array(),
    );

    /**
     *
     * Array of JavaScript files needed to provide specified functionality.
     *
     * @var array
     *
     */
    public $files;

    /**
     *
     * Array of CSS files required by a JavaScript class.
     *
     * @var array
     *
     */
    public $styles;

    /**
     *
     * Array of inline JavaScript needed to provide specified functionality.
     *
     * @var array
     *
     */
    public $scripts;

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
        $this->reset();
    }

    /**
     *
     * Build and return JavaScript for page header.
     *
     * @return string Block of JavaScript with <script src ...> for view-defined
     * script requirements.
     *
     */
    public function fetchFiles()
    {
        $js = '';

        if (!empty($this->files)) {
            foreach ($this->files as $file) {
                $js .= '    ' . $this->_view->script($file) . "\n";
            }
        }

        return $js;
    }

    /**
     *
     * Build and return list of CSS files for page header.
     *
     * @return string Block of HTML with <style> tags for JavaScript-defined
     * style requirements.
     *
     */
    public function fetchStyles()
    {
        $str = '';

        if (!empty($this->styles)) {
            foreach ($this->styles as $style) {
                $str .= '    ' . $this->_view->style($style) . "\n";
            }
        }

        return $str;
    }

    /**
     *
     * Returns all defined inline scripts. This is a separate fetch method
     * so that any/all external (standalone JS file) scripts required by the
     * App or the View that the inline scripts depend on can be loaded prior to
     * the output of the inline script.
     *
     * @return string All inline JavaScripts
     *
     */
    public function fetchInline()
    {
        // Gather all registered scripts for output
        if (!empty($this->scripts)) {
            $scripts = implode("\n\n", $this->scripts);
            $scripts = trim($scripts);
            $inline = $this->_view->inlineScript($scripts);
        } else {
            $inline = '';
        }

        $params = array('scripts' => $inline);

        // Post an event notification to plugins.
        Solar::registry('event')->notify('fetchInlineScript', $this, $params);

        return $params['scripts'];
    }

    /**
     *
     * Fluent interface.
     *
     * @return Solar_View_Helper_Js
     *
     */
    public function js()
    {
        return $this;
    }

    /**
     *
     * Add the specified JavaScript file to the Helper_Js file list
     * if it's not already present.
     *
     * Paths should be relative to the 'scripts' configuration value for the
     * corresponding Solar_View_Helper class.
     *
     * @param mixed $file Name of .js file to add to the header of the page, or
     * (optionally) an array of files to add.
     *
     * @return Solar_View_Helper_Js
     *
     */
    public function addFile($file)
    {
        if ($this->files === null) {
            $this->files = array();
        }

        if (is_array($file)) {
            $this->files = array_merge($this->files, $file);
        } elseif ($file !== null && !in_array($file, $this->files, true)) {
            $this->files[] = $file;
        }

        return $this;
    }

    /**
     *
     * Add the specified CSS file to the Helper_Js styles list
     * if it's not already present.
     *
     * Paths should be releative to the 'styles' configuration value for the
     * corresponding Solar_View_Helper class.
     *
     * @param mixed $file Name of .css file to add to the header of the page, or
     * (optionally) an array of files to add.
     *
     * @return Solar_View_Helper_Js
     *
     */
    public function addStyle($file)
    {
        if ($this->files === null) {
            $this->files = array();
        }

        if (is_array($file)) {
            $this->styles = array_merge($this->styles, $file);
        } elseif ($file !== null && !in_array($file, $this->styles, true)) {
            $this->styles[] = $file;
        }

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
        $this->scripts[] = $src;
        return $this;
    }

    /**
     *
     * Resets the helper entirely.
     *
     * @return object Solar_View_Helper_Js
     *
     */
    public function reset()
    {
        $this->files = array();
        $this->scripts = array();
        $this->styles = array();

        return $this;
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
