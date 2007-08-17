<?php
/**
 *
 * Factory helper for flexible pagination.
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
 * Factory helper for flexible pagination.
 *
 * @category Lux
 *
 * @package Lux_View
 *
 */
class Lux_View_Helper_Pager extends Solar_View_Helper
{
    /**
     *
     * User-provided configuration values. Keys are...
     *
     * `adapter`
     * : (string) Name of the adapter used to build the pagination.
     *
     * @var array
     *
     */
    protected $_Lux_View_Helper_Pager = array(
        'adapter' => 'sliding',
    );

    /**
     *
     * Pager Factory.
     *
     * @param string $adapter Adapter name. Overrides the one defined in config.
     *
     * @param array $config Adapter configuration.
     *
     * @return Lux_View_Helper_Pager_Adapter Pager adapter.
     *
     */
    public function pager($adapter = null, $config = array())
    {
        if(!$adapter) {
            $adapter = $this->_config['adapter'];
        }

        // Format the adapter name.
        $adapter = 'Pager_Adapter_' . ucfirst($adapter);

        // Return the adapter.
        return $this->_view->newHelper($adapter, $config);
    }
}