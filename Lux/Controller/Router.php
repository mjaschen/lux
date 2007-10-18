<?php
/**
 *
 * Front Controller with built in flexible routing mechanism.
 *
 * This is an extended Solar_Controller_Front which adds the possibility to
 * use RubyOnRails-like "routes", allowing virtually any URI scheme to be used
 * in Solar apps. If no route is defined, it falls back to Solar's default
 * front controller.
 *
 * The logic to find a matching route was adapted from Zend Framework's
 * Zend_Controller_Router_Rewrite. The fetch and route logic was adapted from
 * Solar_Controller_Front.
 *
 * @category Lux
 *
 * @package Lux_Controller
 *
 * @author Paul M. Jones <pmjones@solarphp.com>
 *
 * @author Rodrigo Moraes <rodrigo.moraes@gmail.com>
 *
 * @author Zend Framework team.
 *
 * @copyright Copyright (c) 2006 Zend Technologies USA Inc. (http://zend.com)
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 *
 * @version $Id$
 *
 */

/**
 *
 * Front Controller with built in flexible routing mechanism.
 *
 * @category Lux
 *
 * @package Lux_Controller
 *
 */
class Lux_Controller_Router extends Solar_Controller_Front
{
    /**
     *
     * User-provided configuration values.
     *
     * Keys are ...
     *
     * `routes`
     * : (array) Routes definitions in an array format. An example for the
     * default route class, Lux_Controller_Route_Default, is:
     *
     * (string) name => array(
     *     'route'        => (string) map,
     *     ['defaults'    => (array) defaults,]
     *     ['reqs'        => (array) requirements,]
     *     ['route_class' => (string) route class name]
     * )
     *
     * For example:
     *
     * {{code: php
     *     $routes = array();
     *     // Blog archives
     *     $routes['blog_archive'] = array(
     *         'route'    => 'posts/archive/:year/:month/:day',
     *         'defaults' => array(
     *             'controller' => 'blog',
     *             'action'     => 'archive',
     *             'year'       => null,
     *             'month'      => null,
     *             'day'        => null,
     *          ),
     *     );
     * }}
     *
     * `route_class`
     * : (string) Default route class.
     *
     * `compat`
     * : (bool) If true, shift the controller/action names from the uri path
     *   before passing it to the page controller, when using routes.
     *   This makes routes compatible with existing Solar apps, as the $_info
     *   array in the page controller will be the same. Also the paths are not
     *   necessary there, so it is a good idea to keep this set to true.
     *
     * @var array
     *
     */
    protected $_Lux_Controller_Router = array(
        'routes'      => null,
        'route_class' => 'Lux_Controller_Route_Default',
        'compat'      => true,
    );

    /**
     *
     * Array of route objects, keyed by name.
     *
     * @var array
     *
     */
    protected $_routes = array();

    /**
     *
     * Currently matched route name.
     *
     * @var string The route name.
     *
     */
    protected $_matched = null;

    /**
     *
     * Parameters from the currently matched route, if any.
     *
     * @var array
     *
     */
    protected $_params;

    /**
     *
     * Fetches the output of a module/controller/action/info specification URI.
     *
     * If routes are defined, it will try to match the path info against them.
     * Otherwise it will fallback to Solar_Controller_Front.
     *
     * @param Solar_Uri_Action|string $spec An action URI for the front
     * controller.  E.g., 'bookmarks/user/pmjones/php+blog?page=2'. When
     * empty (the default) uses the current URI.
     *
     * @return string The output of the page action.
     *
     */
    public function fetch($spec = null)
    {
        if ($spec instanceof Solar_Uri_Action) {
            // A URI was passed directly.
            $uri = $spec;
        } else {
            // User spec is a URI string; if empty, is the current URI.
            $uri = Solar::factory('Solar_Uri_Action', array(
                'uri' => (string) $spec,
            ));
        }

        // Add routes defined in config.
        if($this->_config['routes']) {
            $this->addRoutes($this->_config['routes']);
        }

        // Are we using routes?
        if(!empty($this->_routes)) {
            // Get the full path info.
            $path = implode('/', $uri->path);

            // Search for a matching route.
            foreach(array_reverse($this->_routes) as $name => $route) {
                $params = $route->match($path);
                if($params) {
                    // Found a route. Don't proceed with Solar_Controller_Front.
                    $this->_matched = $name;
                    return $this->_route($uri, $params);
                }
            }
        }

        // No routes. Use Solar_Controller_Front normal fetching.
        return parent::fetch($uri);
    }

    /**
     *
     * Fetches the output of a module/controller/action/info specification URI
     * using the currently matched route.
     *
     * @param Solar_Uri_Action $uri An action URI for the front controller.
     *
     * @param array $params Parameters from the matched route.
     *
     * @return string The output of the page action.
     *
     */
    protected function _route($uri, $params)
    {
        if(!isset($params['controller'])) {
            // No controller is defined. Go back to Solar_Controller_Front.
            return parent::fetch($uri);
        }

        // Set controller.
        $page = ucfirst($params['controller']);

        if(isset($params['module'])) {
            // Prepend the module name.
            $page = ucfirst($params['module']) . '_' . $page;
        }

        // Try to get a class from the module/controller combination.
        $class = $this->_getPageClass($page);

        if(!$class) {
            // Module/controller not found. Go back to Solar_Controller_Front.
            return parent::fetch($uri);
        }

        if($this->_config['compat']) {
            // Compatibility: take the page name off the top of the path.
            array_shift($uri->path);
        }

        if(isset($params['action'])) {
            if($this->_config['compat']) {
                // Compatibility: remove the action from the top of the path.
                array_shift($uri->path);
            }

            // Add the action value taken from the route.
            array_unshift($uri->path, $params['action']);
        }

        // Set the route parameters.
        $this->_params = $params;

        // Instantiate the page controller class.
        $obj = Solar::factory($class);

        // Inject the front controller.
        $obj->setFrontController($this);

        // Fetch contents.
        return $obj->fetch($uri);
    }

    // -------------------------------------------------------------------------
    //
    // Get routes and related.
    //
    // -------------------------------------------------------------------------

    /**
     *
     * Returns a defined route.
     *
     * @param string $name Route name. If not defined, returns the currently
     * matched route, if any.
     *
     * @return object Route instance.
     *
     */
    public function getRoute($name = null)
    {
        if($name && isset($this->_routes[$name])) {
            return $this->_routes[$name];
        } elseif($this->_matched && isset($this->_routes[$this->_matched])) {
            return $this->_routes[$this->_matched];
        }

        return null;
    }

    /**
     *
     * Returns all defined routes.
     *
     * @return array List of routes.
     *
     */
    public function getRoutes()
    {
        return $this->_routes;
    }

    /**
     *
     * Returns the currently matched route name.
     *
     * @return string The route name.
     *
     */
    public function getMatched()
    {
        return $this->_matched;
    }

    /**
     *
     * Returns the parameters for the currently matched route.
     *
     * @return array Matched route parameters.
     *
     */
    public function getParams()
    {
        return $this->_params;
    }

    // -------------------------------------------------------------------------
    //
    // Set routes.
    //
    // -------------------------------------------------------------------------

    /**
     *
     * Adds a route to the routes list. Creates it if necessary.
     *
     * @param string $name Route name.
     *
     * @param array|object $spec Route specification or a Route object.
     *
     * @param string $class Use this class to create the route, when using
     * a spec. However, if the spec has a key 'route_class', the class will be
     * the correspondent value and $class will be ignored. This allows that
     * routes defined in config specify which route class should be used to
     * build them.
     *
     */
    public function addRoute($name, $spec, $class = null)
    {
        if(is_array($spec)) {
            // Create a new route.
            if(isset($spec['route_class'])) {
                // Use the class set in the spec, then remove it.
                $class = $spec['route_class'];
                unset($spec['route_class']);
            } elseif(!$class) {
                // Use the default route class.
                $class = $this->_config['route_class'];
            }

            $spec = Solar::factory($class, $spec);
        }

        $this->_routes[$name] = $spec;
    }

    /**
     *
     * Adds a collection of routes.
     *
     * @param array $routes Named routes: 'name' => array() | Route object.
     *
     * @param string $class Class to build the routes, if using an array spec.
     *
     */
    public function addRoutes($routes, $class = null)
    {
        foreach ((array) $routes as $name => $route) {
            $this->addRoute($name, $route, $class);
        }
    }

    /**
     *
     * Sets all routes at once.
     *
     * @param array List of route objects keyed by route name.
     *
     */
    public function setRoutes($routes)
    {
        $this->_routes = (array) $routes;
    }
}