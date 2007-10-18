<?php
/**
 *
 * Generates actions / anchors / href's from routes.
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
 * Generates actions / anchors / href's from routes.
 *
 * @category Lux
 *
 * @package Lux_View
 *
 */
class Lux_View_Helper_RouteUri extends Solar_View_Helper
{
    /**
     *
     * User-provided configuration values.
     *
     * Keys are ...
     *
     * `app`
     * : (string) A Solar registry key for the current application.
     *
     */
    protected $_Lux_View_Helper_RouteUri = array(
        'app' => 'app',
    );

    /**
     *
     * A reference to the controller which stores the route definitions.
     *
     * @var Lux_Controller_Router.
     *
     */
    protected $_front;

    /**
     *
     * Assembled href.
     *
     * @var string
     *
     */
    protected $_href;

    /**
     *
     * Assembled URI as a Solar_Uri_Action object.
     *
     * @var Solar_Uri_Action
     *
     */
    protected $_uri;

    /**
     *
     * Constructor.
     *
     * @param array $config User-defined configuration values.
     *
     */
    public function __construct($config = null)
    {
        parent::__construct($config);

        // Set the front controller.
        if(Solar::isRegistered($this->_config['app'])) {
            $app = Solar::registry($this->_config['app']);
            if(method_exists($app, 'getFrontController')) {
                $this->_front = $app->getFrontController();
            }
        }

        // Start an URI object.
        $this->_uri = Solar::factory('Solar_Uri_Action');
    }

    /**
     *
     * Assembles user submitted parameters forming a URI defined by a route.
     *
     * @param array $data An array of variable and value pairs used as
     * parameters.
     *
     * @param string $name Route name.
     *
     * @param bool $reset Whether or not to set route defaults with those
     * provided in $data.
     *
     * @return Lux_View_Helper_RouteUri.
     *
     */
    public function routeUri($data = array(), $name = null, $reset = false)
    {
        if(!$this->_front) {
            // Front controller is not set.
            $this->_href = null;
            return $this;
        }

        // Get the named route.
        $route = $this->_front->getRoute($name);

        if(!$route) {
            // No route found.
            $this->_href = null;
            return $this;
        }

        // Assemble the URI.
        $data = (array) $data;
        $this->_href = $route->assemble($data, $reset);

        // Done.
        return $this;
    }

    /**
     *
     * Returns an action anchor, or just an action href.
     *
     * If the $text link text is empty, will return only the href
     * value instead of an <a href="">...</a> tag.
     *
     * @param string $text A locale translation key.
     *
     * @param string $attribs Additional attributes for the anchor.
     *
     * @return string
     *
     */
    public function action($text = null, $attribs = null)
    {
        if($this->_href) {
            return $this->_view->action($this->href(), $text, $attribs);
        }
    }

    /**
     *
     * Returns an action anchor with a full URL, or just an action href.
     *
     * If the $text link text is empty, will return only the href
     * value instead of an <a href="">...</a> tag.
     *
     * @param string $text A locale translation key.
     *
     * @param string $attribs Additional attributes for the anchor.
     *
     * @return string
     *
     */
    public function anchor($text = null, $attribs = null)
    {
        if($this->_href) {
            return $this->_view->anchor($this->href(true), $text, $attribs);
        }
    }

    /**
     *
     * Returns the current action href.
     *
     * @param bool $full If true, returns a full URI with scheme,
     * user, pass, host, and port.  Otherwise, just returns the
     * path, query, and fragment.  Default false.
     *
     * @return string
     *
     */
    public function href($full = false)
    {
        if($this->_href) {
            return $this->_uri->quick($this->_href, $full);
        }
    }
}