<?php
/**
 *
 * Cycles through a series of values. Based on the 'cycle' Savant 2 plugin.
 *
 * [Cycle]: http://phpsavant.com/yawiki/index.php?area=Savant2&page=PluginCycle
 *
 * @category Lux
 *
 * @package Lux_View
 *
 * @author Paul M. Jones <pmjones@ciaweb.net>
 *
 * @license LGPL http://www.gnu.org/copyleft/lesser.html
 *
 * @version $Id$
 *
 */

/**
 *
 * Cycles through a series of values.
 *
 * @category Lux
 *
 * @package Lux_View
 *
 */
class Lux_View_Helper_Cycle extends Solar_View_Helper
{
    /**
     *
     * Cycles through a series of values.
     *
     * @param array $cycle List of cycle values.
     *
     * @param int $iteration The iteration number for the cycle.
     *
     * @param int $repeat The number of times to repeat each cycle value.
     *
     * @return mixed The value of the cycle iteration.
     *
     */
    function cycle($cycle, $iteration, $repeat = 1)
    {
        $cycle = (array) $cycle;

        // Prevent divide-by-zero errors.
        if($repeat <= 1) {
            return $cycle[$iteration % count($cycle)];
        }

        // Return the perper value for iteration and repetition.
        return $cycle[($iteration / $repeat) % count($cycle)];
    }
}