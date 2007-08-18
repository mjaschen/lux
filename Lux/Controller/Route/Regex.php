<?php
/**
 *
 * Regex Route. Ported from Zend Framework.
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
 * Regex Route. Ported from Zend Framework.
 *
 * @category Lux
 *
 * @package Lux_Controller
 *
 * @subpackage Lux_Controller_Route
 *
 */
class Lux_Controller_Route_Regex extends Solar_Base
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
     * : (array) Defaults to map preg_match() result positions to route
     *   variables.
     *
     * `map`
     * : (array) An associative array of regex subpatterns to parameter named
     * keys.
     *
     * `reverse`
     * : (string) A string parsable by sprintf(), used to assemble a URI for
     * this route.
     *
     * @var array
     *
     */
    protected $_Lux_Controller_Route_Regex = array(
        'route'    => null,
        'defaults' => array(),
        'map'      => array(),
        'reverse'  => null,
    );

    /**
     *
     * Regular expression used to match the path.
     *
     * @var string
     *
     */
    protected $_regex;

    /**
     *
     * Defaults to map preg_match() result positions to route variables.
     *
     * @var array
     *
     */
    protected $_defaults;

    /**
     *
     * An associative array of regex subpatterns to parameter named keys.
     *
     * @var array
     *
     */
    protected $_map;

    /**
     *
     * A string parsable by sprintf(), used to assemble a URI for this route.
     *
     * @var string
     *
     */
    protected $_reverse;

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
     * Constructor. Prepares the route for mapping.
     *
     * @param array $config User-provided configuration values.
     *
     */
    public function __construct($config = null)
    {
        // Do the "real" construction.
        parent::__construct($config);

        $this->_regex = '#^' . $this->_config['route'] . '$#i';
        $this->_defaults = (array) $this->_config['defaults'];
        $this->_map = (array) $this->_config['map'];
        $this->_reverse = $this->_config['reverse'];
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
        $path = trim(urldecode($path), '/');
        $res = preg_match($this->_regex, $path, $values);

        if ($res === 0) return false;

        // array_filter_key()?
        // Why isn't this in a standard PHP function set yet? :)
        foreach ($values as $i => $value) {
            if (!is_int($i) || $i === 0) {
                unset($values[$i]);
            }
        }

        $this->_values = $values;

        $values = $this->_getMappedValues($values);
        $defaults = $this->_getMappedValues($this->_defaults, false, true);

        $return = $values + $defaults;

        return $return;
    }

    /**
     *
     * Maps numerically indexed array values to it's associative mapped
     * counterpart. Or vice versa. Uses user provided map array which consists
     * of index => name parameter mapping. If map is not found, it returns
     * original array.
     *
     * Method strips destination type of keys form source array. Ie. if source
     * array is indexed numerically then every associative key will be stripped.
     * Vice versa if reversed is set to true.
     *
     * @param array $values Indexed or associative array of values to map.
     *
     * @param bool $reversed False means translation of index to association.
     * True means reverse.
     *
     * @param bool $preserve Should wrong type of keys be preserved or stripped.
     *
     * @return array An array of mapped values.
     *
     */
    protected function _getMappedValues($values, $reversed = false,
        $preserve = false)
    {
        if (count($this->_map) == 0) {
            return $values;
        }

        $return = array();

        foreach ($values as $key => $value) {
            if (is_int($key) && !$reversed) {
                if (array_key_exists($key, $this->_map)) {
                    $index = $this->_map[$key];
                } elseif($index !== array_search($key, $this->_map)) {
                    $index = $key;
                }
                $return[$index] = $values[$key];
            } elseif ($reversed) {
                if(!is_int($key)) {
                    $index = array_search($key, $this->_map, true);
                } else {
                    $index = $key;
                }

                if (false !== $index) {
                    $return[$index] = $values[$key];
                }
            } elseif ($preserve) {
                $return[$key] = $value;
            }
        }

        return $return;
    }

    /**
     *
     * Assembles a URL path defined by this route.
     *
     * @param array $data An array of name (or index) and value pairs used as
     * parameters.
     *
     * @return string Route path with user submitted parameters.
     *
     */
    public function assemble($data = array())
    {
        if ($this->_reverse === null) {
            throw $this->_exception('ERR_REVERSED_ROUTE_NOT_SPECIFIED');
        }

        $data = $this->_getMappedValues($data, true, false);
        $data += $this->_getMappedValues($this->_defaults, true, false);
        $data += $this->_values;

        ksort($data);

        $return = @vsprintf($this->_reverse, $data);

        if ($return === false) {
            throw $this->_exception('ERR_CANNOT ASEMBLE_ROUTE');
        }

        return $return;
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
