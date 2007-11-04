<?php
/**
 *
 * Extended Route_Regex that can include hostname matches in the results
 * and/or ignore matched hostnames.
 *
 * Useful to match subdomains and/or ignore others. It can set or override
 * values in the resulted map with hostname matches (e.g., a subdomain becomes
 * a 'site_section' value in the route result).
 *
 * @category Lux
 *
 * @package Lux_Controller
 *
 * @subpackage Lux_Controller_Route
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
 * Extended Route_Regex that can include hostname matches in the results
 * and/or ignore matched hostnames.
 *
 * @category Lux
 *
 * @package Lux_Controller
 *
 * @subpackage Lux_Controller_Route
 *
 */
class Lux_Controller_Route_RegexHost extends Lux_Controller_Route_Regex
{
    /**
     *
     * User-provided configuration values.
     *
     * Keys are ...
     *
     * `host_regex`
     * : (string) Regex to match hostnames.
     *
     * `host_map`
     * : (array) Keys to set in the resulted route, mapping to hostname matches.
     * Can override keys defined in Lux_Controller_Route_Regex::match().
     *
     * `host_reverse`
     * : (string) A string parsable by sprintf(), used to assemble the hostname.
     *
     * `host_ignore`
     * : (string) A regex used to ignore matching hostnames.
     *
     * @var array
     *
     */
    protected $_Lux_Controller_Route_RegexHost = array(
        'host_regex'   => null,
        'host_map'     => null,
        'host_reverse' => null,
        'host_ignore'  => null,
    );

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
        // 1. Check if the hostname is valid.
        if ($this->_config['host_regex']) {
            $request = Solar_Registry::get('request');
            $host = $request->server('HTTP_HOST');

            if ($this->_config['host_ignore']) {
                $regex = '#^' . $this->_config['host_ignore'] . '$#ix';
                if (preg_match($regex, $host)) {
                    // Regex matched a hostname that must be ignored.
                    return false;
                }
            }

            $regex = '#^' . $this->_config['host_regex'] . '$#ix';
            $res = preg_match($regex, $host, $host_values);

            if (! $res) {
                // Regex didn't match a valid hostname.
                return false;
            }

            foreach ($host_values as $i => $value) {
                if (!is_int($i) || $i === 0) {
                    unset($host_values[$i]);
                }
            }
        }

        // 2. Check if the path is valid.
        $values = parent::match($path);

        if ($values === false) {
            // Path is not valid.
            return false;
        }

        if ($this->_config['host_regex']) {
            return $this->_getMappedHost($values, $host_values);
        }

        return $values;
    }

    /**
     *
     * Override or set matches found in the hostname.
     *
     * @param array $values Route matched values.
     *
     * @param array $host_values Matches found in the hostname.
     *
     * @return array Route results combined with hostname results.
     *
     */
    protected function _getMappedHost($values, $host_values)
    {
        if (!$this->_config['host_regex'] || !$this->_config['host_map']) {
            return $values;
        }

        $map = array_flip((array) $this->_config['host_map']);

        foreach($map as $key => $index) {
            if (array_key_exists($index, $host_values)) {
                $value = $host_values[$index];
            } else {
                $value = null;
            }

            // Override values with the ones from the host match
            // or add new keys.
            $values[$key] = $value;
        }

        return $values;
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
        $res = '';

        if ($this->_config['host_regex']) {
            // Assemble host definitions.
            if (! $this->_config['host_reverse']) {
                throw $this->_exception('ERR_HOST_REVERSE_ROUTE_NOT_SPECIFIED');
            }

            // Switch current map to use the host map.
            $temp_map = $this->_map;
            $this->_map = (array) $this->_config['host_map'];

            // Map the host related values.
            $host_data = $this->_getMappedValues($data, true, false);

            // Put the original map back.
            $this->_map = $temp_map;

            // Replace the values in the reverse route.
            $res = @vsprintf($this->_config['host_reverse'], $host_data);

            if ($res === false) {
                throw $this->_exception('ERR_ASSEMBLE_FAILED');
            }

            // With a hostname, always return a full uri.
            $res = 'http://' . $res . '/';
        }

        // Append the uri.
        $res .= parent::assemble($data);

        // Done.
        return $res;
    }
}