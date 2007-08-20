<?php
/**
 *
 * Color utilities.
 *
 * @category Lux
 *
 * @package Lux_Color
 *
 * @author Rodrigo Moraes <rodrigo.moraes@gmail.com>
 *
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 *
 * @version $Id$
 *
 */

/**
 *
 * Color utilities.
 *
 * @category Lux
 *
 * @package Lux_Color
 *
 */
class Lux_Color extends Solar_Base
{
    /**
     *
     * Converts hexadecimal colors to RGB.
     *
     * @param string $hex Hexadecimal value. Accepts values with 3 or 6
     * characters, with or without #, e.g., CCC, #CCC, CCCCCC or #CCCCCC.
     *
     * @return array RGB values: 0 => R, 1 => G, 2 => B
     *
     */
    public function hex2rgb($hex)
    {
        // Remove #.
        if (strpos($hex, '#') === 0) {
            $hex = substr($hex, 1);
        }

        // Duplicate the values.
        if(strlen($hex) == 3) {
            $hex .= $hex;
        }

        if(strlen($hex) != 6) {
            // Invalid hex value.
            return null;
        }

        // Convert each tuple to decimal.
        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));

        return array($r, $g, $b);
    }

    /**
     *
     * Converts RGB colors to hexadecimal.
     *
     * @param array $rgb RGB values: 0 => R, 1 => G, 2 => B
     *
     * @return string Hexadecimal value with six characters, e.g., CCCCCC.
     *
     */
    public function rgb2hex($rgb)
    {
        if(!is_array($rgb) || count($rgb) != 3) {
            // Invalid rgb values.
            return null;
        }

        $hex = '';

        foreach($rgb as $key => $value) {
            $value = (int) $value;

            if($value < 0) {
                $value = 0;
            } elseif($value > 255) {
                $value = 255;
            }

            // Convert to hexadecimal.
            $value = dechex($value);
            // Ensure that values smaller than 10 will have a leading zero.
            $hex .= strtoupper(sprintf('%02s', $value));
        }

        return $hex;
    }
}