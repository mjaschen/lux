<?php
/**
 *
 * Route used to manage static URIs. Ported from Zend Framework.
 *
 * It's a lot faster compared to the standard Route implementation.
 *
 * @category Lux
 *
 * @package Lux_Controller
 *
 * @subpackage Lux_Controller_Route
 *
 * @author Zend Framework.
 *
 * @author Rodrigo Moraes (Solar port) <rodrigo.moraes@gmail.com>
 *
 * @copyright Copyright (c) 2005-2007 Zend Technologies USA Inc. (http://www.zend.com)
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 *
 * @version $Id$
 *
 */

/**
 *
 * Route used to manage static URIs. Ported from Zend Framework.
 *
 * It's a lot faster compared to the standard Route implementation.
 *
 * @category Lux
 *
 * @package Lux_Controller
 *
 * @subpackage Lux_Controller_Route
 *
 */
class Lux_Controller_Route_Static extends Solar_Base
{
    /**
     *
     * User-provided configuration values.
     *
     * Keys are ...
     *
     * `route`
     * : (string) Map used to match with path info.
     *
     * `defaults`
     * : (array) Defaults for map variables with keys as variable names.
     *
     * @var array
     *
     */
    protected $_Lux_Controller_Route_Static = array(
        'route'    => null,
        'defaults' => array(),
    );

    /**
     *
     * The static route.
     *
     * @var string
     *
     */
    protected $_route;

    /**
     *
     * Defaults to map route variables with keys as variable names.
     *
     * @var array
     *
     */
    protected $_defaults;

    /**
     *
     * Constructor. Prepares the route for mapping.
     *
     * @param array $config User-provided configuration values.
     *
     */
    public function __construct($config = null)
    {
        // Do the "real" construction.
        parent::__construct($config);

        $this->_route = trim($this->_config['route'], '/');
        $this->_defaults = (array) $this->_config['defaults'];
    }

    /**
     *
     * Matches a user submitted path with a previously defined route.
     * Assigns and returns an array of defaults on a successful match.
     *
     * @param string $path Path used to match against this routing map.
     *
     * @return array|false An array of assigned values or a false on a mismatch.
     *
     */
    public function match($path)
    {
        if (trim($path, '/') == $this->_route) {
            return $this->_defaults;
        }

        return false;
    }

    /**
     *
     * Assembles a URL path defined by this route.
     *
     * @param array $data An array of variable and value pairs used as parameters.
     *
     * @return string Route path with user submitted parameters.
     *
     */
    public function assemble($data = array())
    {
        return $this->_route;
    }

    /**
     *
     * Returns a single parameter of route's defaults.
     *
     * @param string $name Array key of the parameter.
     *
     * @return string Previously set default.
     *
     */
    public function getDefault($name)
    {
        if (isset($this->_defaults[$name])) {
            return $this->_defaults[$name];
        }
        return null;
    }

    /**
     *
     * Returns an array of defaults.
     *
     * @return array Route defaults.
     *
     */
    public function getDefaults()
    {
        return $this->_defaults;
    }
}
