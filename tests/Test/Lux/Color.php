<?php
class Test_Lux_Color extends Solar_Test
{
    private $_color;

    public function setup()
    {
        $this->_color = Solar::factory('Lux_Color');
    }

    // ------------------------------
    //
    // Hex
    //

    public function testHex2rgb()
    {
        $hex = '00ADEF';
        $rgb = $this->_color->hex2rgb($hex);

        $expect = array(0, 173, 239);
        $this->assertSame($rgb, $expect);
    }

    public function testHex2rgb_2()
    {
        $hex = '#00ADEF';
        $rgb = $this->_color->hex2rgb($hex);

        $expect = array(0, 173, 239);
        $this->assertSame($rgb, $expect);
    }

    public function testHex2rgb_3()
    {
        $hex = '#EEE';
        $rgb = $this->_color->hex2rgb($hex);

        $expect = array(238, 238, 238);
        $this->assertSame($rgb, $expect);
    }

    public function testHex2rgbInvalid()
    {
        $hex = 'invalid';
        $rgb = $this->_color->hex2rgb($hex);

        $this->assertSame($rgb, false);
    }

    public function testHex2hsv()
    {
        $hex = '00ADEF';
        $hsv = $this->_color->hex2hsv($hex);

        // Set the value ranges.
        $hsv[0] = (int) round($hsv[0] * 360);
        $hsv[1] = (int) round($hsv[1] * 100);
        $hsv[2] = (int) round($hsv[2] * 100);

        $expect = array(197, 100, 94);
        $this->assertSame($hsv, $expect);
    }

    public function testHex2hsl()
    {
        $hex = '00ADEF';
        $hsl = $this->_color->hex2hsl($hex);

        // Set the value ranges.
        $hsl[0] = (int) round($hsl[0] * 255);
        $hsl[1] = (int) round($hsl[1] * 255);
        $hsl[2] = (int) round($hsl[2] * 255);

        $expect = array(139, 255, 120);
        $this->assertSame($hsl, $expect);
    }

    // ------------------------------
    //
    // Rgb
    //

    public function testRgb2hex()
    {
        $rgb = array(0, 173, 239);
        $hex = $this->_color->rgb2hex($rgb);

        $expect = '00ADEF';
        $this->assertSame($hex, $expect);
    }

    public function testRgb2hexInvalid()
    {
        $rgb = array(0, 173);
        $hex = $this->_color->rgb2hex($rgb);

        $this->assertSame(false, $hex);
    }

    public function testRgb2hsv()
    {
        $rgb = array(0, 173, 239);
        $hsv = $this->_color->rgb2hsv($rgb);

        // Set the value ranges.
        $hsv[0] = (int) round($hsv[0] * 360);
        $hsv[1] = (int) round($hsv[1] * 100);
        $hsv[2] = (int) round($hsv[2] * 100);

        $expect = array(197, 100, 94);
        $this->assertSame($hsv, $expect);
    }

    public function testRgb2hsl()
    {
        $rgb = array(0, 173, 239);
        $hsl = $this->_color->rgb2hsl($rgb);

        // Set the value ranges.
        $hsl[0] = (int) round($hsl[0] * 255);
        $hsl[1] = (int) round($hsl[1] * 255);
        $hsl[2] = (int) round($hsl[2] * 255);

        $expect = array(139, 255, 120);
        $this->assertSame($hsl, $expect);
    }

    // ------------------------------
    //
    // HSV
    //

    public function testHsv2hex()
    {
        $hsv = array(0, 1, 1);
        $hex = $this->_color->hsv2hex($hsv);

        $expect = 'FF0000';
        $this->assertSame($hex, $expect);
    }

    public function testHsv2rgb()
    {
        $hsv = array(0.5, 0.5, 0.5);
        $rgb = $this->_color->hsv2rgb($hsv);

        // Set the value ranges.
        $rgb[0] = (int) round($rgb[0]);
        $rgb[1] = (int) round($rgb[1]);
        $rgb[2] = (int) round($rgb[2]);

        $expect = array(64, 128, 128);
        $this->assertSame($rgb, $expect);
    }

    public function testHsv2hsl()
    {
        $hsv = array(0.5, 0.5, 0.5);
        $hsl = $this->_color->hsv2hsl($hsv);

        // Set the value ranges.
        $hsl[0] = (int) round($hsl[0] * 255);
        $hsl[1] = (int) round($hsl[1] * 255);
        $hsl[2] = (int) round($hsl[2] * 255);

        $expect = array(128, 85, 96);
        $this->assertSame($hsl, $expect);
    }

    // ------------------------------
    //
    // HSL
    //

    public function testHsl2hex()
    {
        $hsl = array(0, 1, 0.5);
        $hex = $this->_color->hsl2hex($hsl);

        $expect = 'FF0000';
        $this->assertSame($hex, $expect);
    }

    public function testHsl2rgb()
    {
        $hsl = array(0, 1, 0.5);
        $rgb = $this->_color->hsl2rgb($hsl);

        // Set the value ranges.
        $rgb[0] = (int) $rgb[0];
        $rgb[1] = (int) $rgb[1];
        $rgb[2] = (int) $rgb[2];

        $expect = array(255, 0, 0);
        $this->assertSame($rgb, $expect);
    }

    public function testHsl2hsv()
    {
        $hsl = array(0, 1, 0.5);
        $hsv = $this->_color->hsl2hsv($hsl);

        // Set the value ranges.
        $hsv[0] = (int) round($hsv[0] * 360);
        $hsv[1] = (int) round($hsv[1] * 100);
        $hsv[2] = (int) round($hsv[2] * 100);

        $expect = array(0, 100, 100);
        $this->assertSame($hsv, $expect);
    }
}