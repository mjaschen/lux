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
        $this->assertSame(count($values), 1);
        $this->assertSame($values['controller'], 'ctrl');
    }

    public function testRootRoute()
    {
        $config = array(
            'route'      => '',
        );
        $route = Solar::factory('Lux_Controller_Route_Regex', $config);

        $values = $route->match('/');
        $this->assertSame($values, array());
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
        $this->assertSame(count($keys), 2);
        $this->assertSame($keys[0], 'year');
        $this->assertSame($keys[1], 'month');
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
        $this->assertSame(count($values), 2);
        $this->assertSame($values['year'], '2007');
        $this->assertSame($values['month'], '08');

        $values = $route->match('blog/archives/fooo/08');
        $this->assertSame($values, false);
    }

    public function testOptionalVariableMatch()
    {
        $config = array(
            // Matches:
            // optional / + 19** or 20** (year), followed by...
            // optional / + 01 to 12 (month), followed by...
            // optional / + 01 to 31 (day).
            // Doesn't differ months with 28/29/30 days (use php for this)
            'route'    => 'blog/archives(?:/((?:19|20)\d\d)(?:/(0[1-9]|1[012])(?:/(0[1-9]|[12][0-9]|3[01]))?)?)?',
            'map'      => array(
                1 => 'year',
                2 => 'month',
                3 => 'day',
            ),
        );
        $route = Solar::factory('Lux_Controller_Route_Regex', $config);

        $values = $route->match('blog/archives');
        $this->assertSame(count($values), 0);
        $this->assertSame($values, array());

        $values = $route->match('blog/archives/2001');
        $this->assertSame(count($values), 1);
        $this->assertSame($values['year'], '2001');

        $values = $route->match('blog/archives/2001/01');
        $this->assertSame(count($values), 2);
        $this->assertSame($values['year'], '2001');
        $this->assertSame($values['month'], '01');

        $values = $route->match('blog/archives/2001/01/02');
        $this->assertSame(count($values), 3);
        $this->assertSame($values['year'], '2001');
        $this->assertSame($values['month'], '01');
        $this->assertSame($values['day'], '02');
    }

    public function testMappedVariableMatchWithDefaults()
    {
        $config = array(
            // Matches any values in 3 optional paths (application should
            // sanitize and validate them)
            'route'    => 'blog/archives(?:/([^/]*)(?:/([^/]*)(?:/([^/]*))?)?)?',
            'map'      => array(
                1 => 'year',
                2 => 'month',
                3 => 'day',
            ),
            'defaults' => array(
                'year'  => date('Y'),
                'month' => date('m'),
                'day'   => date('d'),
            ),
        );
        $route = Solar::factory('Lux_Controller_Route_Regex', $config);

        $values = $route->match('blog/archives');
        $this->assertSame(count($values), 3);
        $this->assertSame($values['year'], date('Y'));
        $this->assertSame($values['month'], date('m'));
        $this->assertSame($values['day'], date('d'));

        $values = $route->match('blog/archives/foo');
        $this->assertSame(count($values), 3);
        $this->assertSame($values['year'], 'foo');
        $this->assertSame($values['month'], date('m'));
        $this->assertSame($values['day'], date('d'));

        $values = $route->match('blog/archives/foo/bar');
        $this->assertSame(count($values), 3);
        $this->assertSame($values['year'], 'foo');
        $this->assertSame($values['month'], 'bar');
        $this->assertSame($values['day'], date('d'));

        $values = $route->match('blog/archives/foo/bar/baz');
        $this->assertSame(count($values), 3);
        $this->assertSame($values['year'], 'foo');
        $this->assertSame($values['month'], 'bar');
        $this->assertSame($values['day'], 'baz');

        $values = $route->match('blog/archives/foo/bar/baz/ding');
        $this->assertSame($values, false);
    }
}