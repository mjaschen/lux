<?php
class Test_Lux_Controller_Route_Regex extends Solar_Test
{
    public function testStaticMatch()
    {
        $config = array(
            'route' => 'blog/archives',
        );
        $route = Solar::factory('Lux_Controller_Route_Regex', $config);
        $values = $route->match('blog/archives');

        $this->assertSame($values, array());
    }

    public function testStaticNoMatch()
    {
        $config = array(
            'route' => 'blog/archives',
        );
        $route = Solar::factory('Lux_Controller_Route_Regex', $config);

        $values = $route->match('blog');

        $this->assertSame($values, false);
    }

    public function testStaticMatchWithDefaults()
    {
        $config = array(
            'route'    => 'blog/archives',
            'defaults' => array(
                'controller' => 'ctrl',
            ),
        );
        $route = Solar::factory('Lux_Controller_Route_Regex', $config);

        $values = $route->match('blog/archives');

        $this->assertSame(1, count($values));
        $this->assertSame('ctrl', $values['controller']);
    }

    public function testRootRoute()
    {
        $config = array(
            'route'      => '',
        );
        $route = Solar::factory('Lux_Controller_Route_Regex', $config);

        $values = $route->match('/');

        $this->assertSame(array(), $values);
    }

    public function testMap()
    {
        $config = array(
            // Matches 4 digits + / + 2 digits
            'route' => 'blog/archives/(\d{4})/(\d{2})',
            'map'   => array(
                1 => 'year',
                2 => 'month',
            ),
        );
        $route = Solar::factory('Lux_Controller_Route_Regex', $config);

        $values = $route->match('blog/archives/2007/08');
        $keys = array_keys($values);

        $this->assertSame(2, count($keys));
        $this->assertSame('year', $keys[0]);
        $this->assertSame('month', $keys[1]);
    }

    public function testMappedVariableMatch()
    {
        $config = array(
            // Matches 4 digits + / + 2 digits
            'route' => 'blog/archives/(\d{4})/(\d{2})',
            'map'   => array(
                1 => 'year',
                2 => 'month',
            ),
        );
        $route = Solar::factory('Lux_Controller_Route_Regex', $config);

        $values = $route->match('blog/archives/2007/08');

        $this->assertSame(2, count($values));
        $this->assertSame('2007', $values['year']);
        $this->assertSame('08', $values['month']);

        $values = $route->match('blog/archives/fooo/08');
        $this->assertSame(false, $values);
    }

    public function testOptionalVariableMatch()
    {
        $config = array(
            // Matches 19** or 20** years
            // Matches 01 to 12 months
            // Matches 01 to 31 days
            // Doesn't differs months with 28/29/30 days (use script for that)
            'route'    => 'blog/archives(?:/((?:19|20)\d\d))?(?:/(0[1-9]|1[012]))?(?:/(0[1-9]|[12][0-9]|3[01]))?',
            'map'      => array(
                1 => 'year',
                2 => 'month',
                3 => 'day',
            ),
        );
        $route = Solar::factory('Lux_Controller_Route_Regex', $config);

        $values = $route->match('blog/archives');
        $this->assertSame(0, count($values));

        $values = $route->match('blog/archives/2001');
        $this->assertSame(1, count($values));

        $values = $route->match('blog/archives/2001/01');
        $this->assertSame(2, count($values));

        $values = $route->match('blog/archives/2001/01/02');
        $this->assertSame(3, count($values));

        $this->assertSame('2001', $values['year']);
        $this->assertSame('01', $values['month']);
        $this->assertSame('02', $values['day']);
    }

    public function testMappedVariableMatchWithDefaults()
    {
        $config = array(
            // Matches any values in 3 optional paths (application should
            // sanitize and validate them)
            'route'    => 'blog/archives(?:/([^/]*))?(?:/([^/]*))?(?:/([^/]*))?',
            'defaults' => array(
                'year'  => date('Y'),
                'month' => date('m'),
                'day'   => date('d'),
            ),
            'map'      => array(
                1 => 'year',
                2 => 'month',
                3 => 'day',
            ),
        );
        $route = Solar::factory('Lux_Controller_Route_Regex', $config);

        $values = $route->match('blog/archives');

        $this->assertSame(3, count($values));
        $this->assertSame(date('Y'), $values['year']);
        $this->assertSame(date('m'), $values['month']);
        $this->assertSame(date('d'), $values['day']);

        $values = $route->match('blog/archives/foo');

        $this->assertSame(3, count($values));
        $this->assertSame('foo', $values['year']);
        $this->assertSame(date('m'), $values['month']);
        $this->assertSame(date('d'), $values['day']);

        $values = $route->match('blog/archives/foo/bar');

        $this->assertSame(3, count($values));
        $this->assertSame('foo', $values['year']);
        $this->assertSame('bar', $values['month']);
        $this->assertSame(date('d'), $values['day']);

        $values = $route->match('blog/archives/foo/bar/baz');

        $this->assertSame(3, count($values));
        $this->assertSame('foo', $values['year']);
        $this->assertSame('bar', $values['month']);
        $this->assertSame('baz', $values['day']);

        $values = $route->match('blog/archives/foo/bar/baz/ding');
        $this->assertSame(false, $values);
    }
}