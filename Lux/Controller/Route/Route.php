<?php
/**
 *
 * Default Route for Lux_Controller_Router. Ported from Zend Framework.
 *
 * @category Lux
 *
 * @package Lux_Controller
 *
 * @subpackage Lux_Controller_Route
 *
 * @author Zend Framework.
 *
 * @author Rodrigo Moraes <rodrigo.moraes@gmail.com>
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
 * Default Route for Lux_Controller_Router. Ported from Zend Framework.
 *
 * @category Lux
 *
 * @package Lux_Controller
 *
 * @subpackage Lux_Controller_Route
 *
 */
class Lux_Controller_Route_Route extends Solar_Base
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
     * `reqs`
     * : (array) Regular expression requirements for variables (keys as variable
     *   names).
     *
     * @var array
     *
     */
    protected $_Lux_Controller_Route_Route = array(
        'route'    => null,
        'defaults' => array(),
        'reqs'     => array(),
    );

    /**
     *
     * URL variable.
     *
     * @var string
     *
     */
    protected $_url_variable = ':';

    /**
     *
     * URL delimiter.
     *
     * @var string
     *
     */
    protected $_url_delimiter = '/';

    /**
     *
     * Regular expression delimiter.
     *
     * @var string
     *
     */
    protected $_regex_delimiter = '#';

    /**
     *
     * Default regular expression.
     *
     * @var string
     *
     */
    protected $_defaultRegex = null;

    /**
     *
     * Information array for each of the route parts. Each part can have:
     *   'name' (optional): part keyword.
     *   'regex' (optional): a regexp to be matched.
     *
     * @var array
     *
     */
    protected $_parts;

    /**
     *
     * Defaults to map route variables with keys as variable names.
     *
     * @var array
     *
     */
    protected $_defaults = array();

    /**
     *
     * Regular expression requirements for route variables (keys as variable
     * names).
     *
     * @var array
     *
     */
    protected $_requirements = array();

    /**
     *
     * Amount of non-keyword parts in the route.
     *
     * @var int
     *
     */
    protected $_static_count = 0;

    /**
     *
     * List of variable keywords found in the route. For Example:
     *
     * {{code: php
     *     $_path = 'bookmarks/:action';
     *     // keywords will be:
     *     // $_vars = array('action');
     * }}
     *
     * @var array
     *
     */
    protected $_vars = array();

    /**
     *
     * Array of requested parameters. Later it is merged with default
     * parameters, resulting in $this->_values.
     *
     * @var array
     *
     */
    protected $_params = array();

    /**
     *
     * Resulted values from the matched route.
     *
     * @var array
     *
     */
    protected $_values = array();

    /**
     *
     * Constructor.
     *
     * @param array $config User-provided configuration values.
     *
     */
    public function __construct($config = null)
    {
        // Do the "real" construction.
        parent::__construct($config);

        $route = trim($this->_config['route'], $this->_url_delimiter);
        $this->_defaults = (array) $this->_config['defaults'];
        $this->_requirements = (array) $this->_config['reqs'];

        if ($route != '') {
            foreach (explode($this->_url_delimiter, $route) as $pos => $part) {
                if (substr($part, 0, 1) == $this->_url_variable) {
                    $name = substr($part, 1);

                    if(isset($this->_requirements[$name])) {
                        $regex = $this->_requirements[$name];
                    } else {
                        $regex = $this->_defaultRegex;
                    }

                    $this->_parts[$pos] = array(
                        'name' => $name,
                        'regex' => $regex
                    );

                    $this->_vars[] = $name;
                } else {
                    $this->_parts[$pos] = array('regex' => $part);

                    if ($part != '*') {
                        $this->_static_count++;
                    }
                }
            }
        }
    }

    /**
     *
     * Undocumented.
     *
     */
    protected function _getWildcardData($parts, $unique)
    {
        $pos = count($parts);
        if ($pos % 2) {
            $parts[] = null;
        }
        foreach(array_chunk($parts, 2) as $part) {
            list($var, $value) = $part;
            $var = urldecode($var);
            if (!array_key_exists($var, $unique)) {
                $this->_params[$var] = urldecode($value);
                $unique[$var] = true;
            }
        }
    }

    /**
     *
     * Matches a user submitted path with parts defined by a map. Assigns and
     * returns an array of variables on a successful match.
     *
     * @param string $path Path used to match against this routing map.
     *
     * @return array|false An array of assigned values or a false on a mismatch.
     *
     */
    public function match($path)
    {
        $pathStaticCount = 0;
        $defaults = $this->_defaults;

        if (count($defaults)) {
            $unique = array_combine(array_keys($defaults),
                array_fill(0, count($defaults), true));
        } else {
            $unique = array();
        }

        $path = trim($path, $this->_url_delimiter);

        if ($path != '') {

            $path = explode($this->_url_delimiter, $path);

            foreach ($path as $pos => $pathPart) {
                if (!isset($this->_parts[$pos])) {
                    return false;
                }

                if ($this->_parts[$pos]['regex'] == '*') {
                    $parts = array_slice($path, $pos);
                    $this->_getWildcardData($parts, $unique);
                    break;
                }

                $part = $this->_parts[$pos];
                $name = isset($part['name']) ? $part['name'] : null;
                $pathPart = urldecode($pathPart);

                if ($name === null) {
                    if ($part['regex'] != $pathPart) {
                        return false;
                    }
                } elseif ($part['regex'] === null) {
                    if (strlen($pathPart) == 0) {
                        return false;
                    }
                } else {
                    $regex = $this->_regex_delimiter . '^' . $part['regex']
                        . '$' . $this->_regex_delimiter . 'iu';
                    if (!preg_match($regex, $pathPart)) {
                        return false;
                    }
                }

                if ($name !== null) {
                    // It's a variable. Set a value.
                    $this->_values[$name] = $pathPart;
                    $unique[$name] = true;
                } else {
                    $pathStaticCount++;
                }
            }
        }

        $return = $this->_values + $this->_params + $this->_defaults;

        // Check if all static mappings have been met.
        if ($this->_static_count != $pathStaticCount) {
            return false;
        }

        // Check if all map variables have been initialized.
        foreach ($this->_vars as $var) {
            if (!array_key_exists($var, $return)) {
                return false;
            }
        }

        return $return;
    }

    /**
     *
     * Assembles user submitted parameters forming a URL path defined by this
     * route.
     *
     * @param array $data An array of variable and value pairs used as
     * parameters.
     *
     * @param bool $reset Whether or not to set route defaults with those
     * provided in $data.
     *
     * @return string Route path with user submitted parameters.
     *
     */
    public function assemble($data = array(), $reset = false)
    {
        $url = array();
        $flag = false;

        foreach ((array) $this->_parts as $key => $part) {
            $resetPart = false;
            if (isset($part['name']) && array_key_exists($part['name'], $data)
                && $data[$part['name']] === null) {
                $resetPart = true;
            }

            if (isset($part['name'])) {
                if (isset($data[$part['name']]) && !$resetPart) {
                    $url[$key] = $data[$part['name']];
                    unset($data[$part['name']]);
                } elseif (!$reset && !$resetPart
                    && isset($this->_values[$part['name']])) {
                    $url[$key] = $this->_values[$part['name']];
                } elseif (!$reset && !$resetPart
                    && isset($this->_params[$part['name']])) {
                    $url[$key] = $this->_params[$part['name']];
                } elseif (isset($this->_defaults[$part['name']])) {
                    $url[$key] = $this->_defaults[$part['name']];
                } else {
                    throw $this->_exception(
                        'ERR_PART_IS_NOT_SPECIFIED',
                        array('name' => $part['name'])
                    );
                }
            } else {
                if ($part['regex'] != '*') {
                    $url[$key] = $part['regex'];
                } else {
                    if (!$reset) $data += $this->_params;
                    foreach ($data as $var => $value) {
                        if ($value !== null) {
                            $url[$var] = $var . $this->_url_delimiter . $value;
                            $flag = true;
                        }
                    }
                }
            }
        }

        $return = '';

        foreach (array_reverse($url, true) as $key => $value) {
            if ($flag || !isset($this->_parts[$key]['name'])
                || $value !== $this->getDefault($this->_parts[$key]['name'])) {
                $return = '/' . $value . $return;
                $flag = true;
            }
        }

        return trim($return, '/');
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
