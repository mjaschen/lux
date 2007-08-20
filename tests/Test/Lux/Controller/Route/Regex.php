<?php
// Adapted from Zend_Controller_Router_Route_RegexTest.
class Test_Lux_Controller_Route_Regex extends Solar_Test
{
    public function testStaticMatch()
    {
        $config = array(
            'route' => 'blog/archives',
        );
        $route = Solar::factory('Lux_Controller_Route_Regex', $config);

        $params = $route->match('blog/archives');
        $this->assertSame($params, array());
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

        $params = $route->match('blog/archives');
        $this->assertSame(count($params), 1);
        $this->assertSame($params['controller'], 'ctrl');
    }

    public function testStaticMatchRoot()
    {
        $config = array(
            'route'      => '',
        );
        $route = Solar::factory('Lux_Controller_Route_Regex', $config);

        $params = $route->match('/');
        $this->assertSame($params, array());
    }

    public function testStaticMatchFailure()
    {
        $config = array(
            'route' => 'blog/archives',
        );
        $route = Solar::factory('Lux_Controller_Route_Regex', $config);

        $params = $route->match('blog');
        $this->assertSame($params, false);
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

        $params = $route->match('blog/archives/2007/08');
        $keys = array_keys($params);
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

        $params = $route->match('blog/archives/2007/08');
        $this->assertSame(count($params), 2);
        $this->assertSame($params['year'], '2007');
        $this->assertSame($params['month'], '08');

        $params = $route->match('blog/archives/fooo/08');
        $this->assertSame($params, false);
    }

    public function testOptionalMappedVariableMatch()
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

        $params = $route->match('blog/archives');
        $this->assertSame(count($params), 0);
        $this->assertSame($params, array());

        $params = $route->match('blog/archives/2001');
        $this->assertSame(count($params), 1);
        $this->assertSame($params['year'], '2001');

        $params = $route->match('blog/archives/2001/01');
        $this->assertSame(count($params), 2);
        $this->assertSame($params['year'], '2001');
        $this->assertSame($params['month'], '01');

        $params = $route->match('blog/archives/2001/01/02');
        $this->assertSame(count($params), 3);
        $this->assertSame($params['year'], '2001');
        $this->assertSame($params['month'], '01');
        $this->assertSame($params['day'], '02');
    }

    public function testOptionalMappedVariableMatchWithDefaults()
    {
        $config = array(
            // Matches any values in 3 optional paths (application should
            // sanitize and validate them)
            'route'    => 'blog/archives(?:/([^/]*))?(?:/([^/]*))?(?:/([^/]*))?',
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

        $params = $route->match('blog/archives');
        $this->assertSame(count($params), 3);
        $this->assertSame($params['year'], date('Y'));
        $this->assertSame($params['month'], date('m'));
        $this->assertSame($params['day'], date('d'));

        $params = $route->match('blog/archives/foo');
        $this->assertSame(count($params), 3);
        $this->assertSame($params['year'], 'foo');
        $this->assertSame($params['month'], date('m'));
        $this->assertSame($params['day'], date('d'));

        $params = $route->match('blog/archives/foo/bar');
        $this->assertSame(count($params), 3);
        $this->assertSame($params['year'], 'foo');
        $this->assertSame($params['month'], 'bar');
        $this->assertSame($params['day'], date('d'));

        $params = $route->match('blog/archives/foo/bar/baz');
        $this->assertSame(count($params), 3);
        $this->assertSame($params['year'], 'foo');
        $this->assertSame($params['month'], 'bar');
        $this->assertSame($params['day'], 'baz');

        $params = $route->match('blog/archives/foo/bar/baz/ding');
        $this->assertSame($params, false);
    }
}