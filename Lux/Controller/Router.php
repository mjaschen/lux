<?php
/**
 *
 * Front Controller with built in flexible routing mechanism.
 *
 * This is an extended Solar_Controller_Front which adds the possibility to
 * use RubyOnRails-like "routes", allowing virtually any URI scheme to be used
 * in Solar apps. If no route is defined, it falls back gracefully to Solar's
 * default front controller.
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
     * `route_class`
     * : (string) Default class used to build routes.
     *
     * `routes`
     * : (array) Routes definitions in an array format. Format is:
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
     *     // Blog entries and archives
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
     * `module_key`
     * : (string) Key for a module definition in routes.
     *
     * `controller_key`
     * : (string) Key for a controller definition in routes.
     *
     * `action_key`
     * : (string) Key for an action definition in routes.
     *
     * `compat`
     * : (bool) If true, shift the controller/action name from the uri path
     *   before assing it to the page controller, when using routes.
     *   This makes routes compatible with existing Solar apps, as the $_info
     *   array will be the same.
     *
     * `add_default`
     * : (bool) True to add a default ":controller/:action/*" route, compatible
     *   with Solar apps.
     *
     * `cache`
     * : (dependency) A Solar_Sql dependency to cache routes.
     *
     * `cache_key`
     * : (string) A key used to cache routes. If null, will use the host name.
     *
     * `delete_cache`
     * : (bool) True to force a cache deletion.
     *
     * @var array
     *
     */
    protected $_Lux_Controller_Router = array(
        // Default route class and routes defined in config.
        'route_class'    => 'Lux_Controller_Route_Route',
        'routes'         => null,
        // Route keys.
        'module_key'     => 'module',
        'controller_key' => 'controller',
        'action_key'     => 'action',
        // Routes compatibility with Solar_Controller_Front.
        'compat'         => true,
        'add_default'    => false,
        // Cache for routes.
        'cache'          => null,
        'cache_key'      => null,
        'delete_cache'   => false,
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
     * Currently matched route.
     *
     * @var string The route name.
     *
     */
    protected $_current_route = null;

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
     * Cache to store routes between page loads.
     *
     * @var Solar_Cache
     *
     */
    protected $_cache;

    /**
     *
     * Constructor.
     *
     * @param array $config User-provided configuration values.
     *
     */
    public function __construct($config)
    {
        // Do the "real" construction.
        parent::__construct($config);

        if($this->_config['cache']) {
            // Get the optional dependency object for caching routes.
            $this->_cache = Solar::dependency(
                'Solar_Cache',
                $this->_config['cache']
            );

            if(!$this->_config['cache_key']) {
                $uri = Solar::factory('Solar_Uri_Action');
                // Create a cache key using the host name.
                $this->_config['cache_key'] = 'Lux_Controller_Router/' .
                    $uri->host;
            }

            // Force cache deletion.
            if($this->_config['delete_cache']) {
                $this->deleteCache();
            }
        }
    }

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

        // Set routes stored in cache?
        if($this->_cache) {
            $cached = $this->_setCachedRoutes();
        } else {
            $cached = false;
        }

        // Add a ":controller/:action/*" route, compatible with Solar apps.
        if(!$cached && $this->_config['add_default']) {
            $this->_addDefaultRoutes();
        }

        // Add routes defined in config.
        if(!$cached && $this->_config['routes']) {
            $this->addRoutes($this->_config['routes']);
        }

        // Save routes to cache?
        if(!$cached && $this->_cache && !$this->_config['delete_cache']) {
            $this->_cache->save($this->_config['cache_key'], $this->_routes);
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
                    $this->_current_route = $name;
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
     * using the current matched route.
     *
     * @param Solar_Uri_Action $uri An action URI for the front controller.
     *
     * @param array $params Parameters from the current route.
     *
     * @return string The output of the page action.
     *
     */
    protected function _route($uri, $params)
    {
        if(!isset($params[$this->_config['controller_key']])) {
            // No controller. Go back to Solar_Controller_Front.
            return parent::fetch($uri);
        }

        // Set controller.
        $page = ucfirst($params[$this->_config['controller_key']]);

        if(isset($params[$this->_config['module_key']])) {
            // Set module.
            $page = ucfirst($params[$this->_config['module_key']]) . '_' . $page;
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

        if(isset($params[$this->_config['action_key']])) {
            if($this->_config['compat']) {
                // Compatibility: remove the action from the top of the path.
                array_shift($uri->path);
            }
            // Add the action value taken from the route.
            array_unshift($uri->path, $params[$this->_config['action_key']]);
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
    // Get routes.
    //
    // -------------------------------------------------------------------------

    /**
     *
     * Returns a defined route.
     *
     * @param string $name Route name.
     *
     * @return Lux_Controller_Route_Route.
     *
     */
    public function getRoute($name)
    {
        if(isset($this->_routes[$name])) {
            return $this->_routes[$name];
        }

        return null;
    }

    /**
     *
     * Returns the currently matched route.
     *
     * @return Lux_Controller_Route_Route.
     *
     */
    public function getCurrentRoute()
    {
        return $this->getRoute($this->_current_route);
    }

    /**
     *
     * Returns the currently matched route name.
     *
     * @return string The route name.
     *
     */
    public function getCurrentRouteName()
    {
        return $this->_current_route;
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
     * Returns the parameters for the currently matched route.
     *
     * @return array Route parameters.
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
     * Sets all routes at once.
     *
     * @param array List of routes.
     *
     */
    public function setRoutes($routes)
    {
        $this->_routes = (array) $routes;
    }

    /**
     *
     * Sets routes fetched from cache, if available.
     *
     * @return bool True if a cache was found, false otherwise.
     *
     */
    protected function _setCachedRoutes()
    {
        $routes = $this->_cache->fetch($this->_config['cache_key']);

        if($routes) {
            $this->setRoutes($routes);
            return true;
        } else {
            return false;
        }
    }

    /**
     *
     * Sets routes fetched from cache, if available.
     *
     * @return bool True if a cache was found, false otherwise.
     *
     */
    public function deleteCache()
    {
        if($this->_cache) {
            $this->_cache->delete($this->_config['cache_key']);
        }
    }

    // -------------------------------------------------------------------------
    //
    // Add routes.
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
     * @param array $routes Named routes: 'name' => spec or Route object.
     *
     * @param string $class Use this class to set the routes, if using a spec.
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
     * Adds a default route to the routes list.
     *
     */
    protected function _addDefaultRoutes()
    {
        // Route for Solar apps compatibility.
        $config = array(
            'route'    => ':controller/:action/*',
            'defaults' => array(
                'controller' => 'hello',
                'action'     => 'main'
            )
        );

        $this->addRoute('default', $config);
    }
}
