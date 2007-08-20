<?php
class Test_Lux_Color extends Solar_Test
{
    private $_color;

    public function setup()
    {
        $this->_color = Solar::factory('Lux_Color');
    }

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

        $this->assertNull($rgb);
    }

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

        $this->assertNull($hex);
    }
}