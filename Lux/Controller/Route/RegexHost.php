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
     * : (array) Map hostname matches to keys to be set in the resulted map.
     *   Can override values for keys defined in Lux_Controller_Route_Regex's,
     *   map, if there are identical keys here and there, so use distinct keys
     *   here.
     *
     * `host_reverse`
     * : (string) A string parsable by sprintf(), used to assemble the hostname.
     *
     * `host_ignore`
     * : (string) A regex used to ignore matching hostnames. The ignore rules
     *   can also be set in 'host_regex' together with the rules for valid
     *   hosts, but 'host_ignore' is kept as an option to make regex'es cleaner.
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
                $regex = '{^' . $this->_config['host_ignore'] . '$}ix';
                if (preg_match($regex, $host)) {
                    // Regex matched a hostname that must be ignored.
                    return false;
                }
            }

            $regex = '{^' . $this->_config['host_regex'] . '$}ix';
            $res = preg_match($regex, $host, $host_values);

            if (! $res) {
                // Regex didn't match a valid hostname.
                return false;
            }
        }

        // 2. Check if the path is valid.
        $values = parent::match($path);

        if ($values === false) {
            // Path is not valid.
            return false;
        }

        // 3. Add values from the hostname match to the results.
        if ($this->_config['host_regex'] && $this->_config['host_map']) {
            // Override or set values using matches from the hostname.
            foreach((array) $this->_config['host_map'] as $index => $key) {
                if (array_key_exists($index, $host_values)) {
                    $values[$key] = $host_values[$index];
                } else {
                    $values[$key] = null;
                }
            }
        }

        // Done.
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

            $host_map = (array) $this->_config['host_map'];
            $host_data = $this->_getMappedValues($data, $host_map, true, false);

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