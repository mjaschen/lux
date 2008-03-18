<?php
/**
 *
 * Returns a Solar_Json object for views.
 *
 * @category Lux
 *
 * @package Lux_View_Helper
 *
 * @author Rodrigo Moraes <rodrigo.moraes@gmail.com>
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 *
 * @version $Id$
 *
 */
class Lux_View_Helper_Json extends Solar_View_Helper
{
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

        $this->_json = Solar::factory('Solar_Json');
    }

    /**
     *
     * Returns a Solar_Json object for views.
     *
     * @return Solar_Json
     *
     */
    public function json()
    {
        return $this->_json;
    }
}