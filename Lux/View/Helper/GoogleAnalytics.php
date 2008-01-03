<?php
/**
 * 
 * Helper for tracking sites with Google Analytics
 * 
 * @category Lux
 * 
 * @package Lux_View
 * 
 * @author Antti Holvikari <anttih@gmail.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id$
 * 
 */

/**
 *
 * Helper for tracking sites with Google Analytics
 *
 * @category Lux
 *
 * @package Lux_View
 *
 */
class Lux_View_Helper_GoogleAnalytics extends Solar_View_Helper {
    
    /**
     *
     * User-provided configuration values. Keys are...
     *
     * `id`
     * : (string) A Google Analytics token which identifies the site.
     * 
     * `enable`
     * : (bool) Enable Google analytics. This is useful when you
     * are developing and don't want to track traffic.
     * 
     * @var array
     *
     */
    protected $_Lux_View_Helper_GoogleAnalytics = array(
        'id'     => null,
        'enable' => true,
        'ssl'    => false,
    );
    
    /**
     * 
     * Creates XHTML for sending analytics data to Google
     * 
     * @return string XHTML
     * 
     */
    public function googleAnalytics($id = null)
    {
        // do nothing if not enabled
        if (! $this->_config['enable']) {
            return;
        }
        
        // take from config by default
        $track = $this->_config['id'];
        if (! is_null($id)) {
            $track = $id;
        }
        
        // use ssl for fetching the JS file?
        $protocol = 'http://www.';
        if ($this->_config['ssl']) {
            $protocol = 'https://ssl.';
        }
        
        $js = $protocol . 'google-analytics.com/ga.js';
        $this->_view->head()->addScriptBase($js);
        
        $xhtml = array();
        
        $xhtml[] = '<script type="text/javascript">';
        $xhtml[] = "var pageTracker = _gat._getTracker(\"$track\");";
        $xhtml[] = 'pageTracker._initData();';
        $xhtml[] = 'pageTracker._trackPageview();';
        $xhtml[] = '</script>';
        
        return implode("\n", $xhtml);
    }
}
