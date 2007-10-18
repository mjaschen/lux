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
     * Converts a hexadecimal color value to RGB.
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

        // Convert each value to decimal.
        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));

        return array($r, $g, $b);
    }

    /**
     *
     * Converts a RGB color value to hexadecimal.
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

    /**
     *
     * Converts a RGB color value to HSV.
     *
     * Based on pseudo-code from http://www.easyrgb.com/math.php
     *
     * @param array $rgb RGB values: 0 => R, 1 => G, 2 => B
     *
     * @return array HSV values: 0 => H, 1 => S, 2 => V
     *
     */
    public function rgb2hsv($rgb)
    {
        $var_r = ($rgb[0] / 255);
        $var_g = ($rgb[1] / 255);
        $var_b = ($rgb[2] / 255);

        // Min. value of RGB
        $var_min = min($var_r, $var_g, $var_b);

        // Max. value of RGB
        $var_max = max($var_r, $var_g, $var_b);

        // Delta RGB value.
        $del_max = $var_max - $var_min;

        $v = $var_max;

        if($del_max == 0) {
            // This is a gray, no chroma
            // HSV results = 0 ÷ 1
            $h = 0;
            $s = 0;
        } else {
            // Chromatic data
            $s = $del_max / $var_max;

            $del_r = ((($var_max - $var_r) / 6) + ($del_max / 2)) / $del_max;
            $del_g = ((($var_max - $var_g) / 6) + ($del_max / 2)) / $del_max;
            $del_b = ((($var_max - $var_b) / 6) + ($del_max / 2)) / $del_max;

            if($var_r == $var_max) {
                $h = $del_b - $del_g;
            } elseif($var_g == $var_max) {
                $h = (1 / 3) + $del_r - $del_b;
            } elseif($var_b == $var_max) {
                $h = (2 / 3) + $del_g - $del_r;
            }

            if($h < 0) {
                $h += 1;
            }
            if($h > 1) {
                $h -= 1;
            }
        }

        return array($h, $s, $v);
    }

    /**
     *
     * Converts a HSV color value to RGB.
     *
     * Based on pseudo-code from http://www.easyrgb.com/math.php
     *
     * @param array $hsv HSV values: 0 => H, 1 => S, 2 => V
     *
     * @return array RGB values: 0 => R, 1 => G, 2 => B
     *
     */
    public function hsv2rgb($hsv)
    {
        $h = $hsv[0];
        $s = $hsv[1];
        $v = $hsv[2];

        if ($s == 0) {
            // HSV values = 0 ÷ 1
            $r = $v * 255;
            $g = $v * 255;
            $b = $v * 255;
        } else {
            $var_h = $h * 6;
            // H must be < 1
            if ( $var_h == 6 ) {
                $var_h = 0;
            }

            $var_i = (int) $var_h;
            $var_1 = $v * (1 - $s);
            $var_2 = $v * (1 - $s * ($var_h - $var_i));
            $var_3 = $v * (1 - $s * (1 - ($var_h - $var_i)));

            if($var_i == 0 ) {
                $var_r = $v;
                $var_g = $var_3;
                $var_b = $var_1;
            } elseif($var_i == 1) {
                $var_r = $var_2;
                $var_g = $v;
                $var_b = $var_1;
            } elseif($var_i == 2) {
                $var_r = $var_1;
                $var_g = $v;
                $var_b = $var_3;
            } elseif($var_i == 3) {
                $var_r = $var_1;
                $var_g = $var_2;
                $var_b = $v;
            } elseif($var_i == 4) {
                $var_r = $var_3;
                $var_g = $var_1;
                $var_b = $v;
            } else {
                $var_r = $v;
                $var_g = $var_1;
                $var_b = $var_2;
            }

            // RGB results = 0 ÷ 255
            $r = $var_r * 255;
            $g = $var_g * 255;
            $b = $var_b * 255;
        }

        return array($r, $g, $b);
    }

    /**
     *
     * Converts a hexadecimal color value to HSV.
     *
     * @param string $hex Hex value.
     *
     * @return array HSV values: 0 => H, 1 => S, 2 => V
     *
     */
    public function hex2hsv($hex)
    {
        return $this->rgb2hsv($this->hex2rgb($hex));
    }

    /**
     *
     * Converts a HSV color value to hexadecimal.
     *
     * @param array HSV values: 0 => H, 1 => S, 2 => V
     *
     * @return string Hexadecimal value with six characters, e.g.: 'CCCCCC'.
     *
     */
    public function hsv2hex($hsv)
    {
        return $this->rgb2hex($this->hsv2rgb($hsv));
    }
}