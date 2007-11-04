<?php
class Test_Lux_Controller_Route_RegexHost extends Solar_Test
{
    public function testStaticMatch()
    {
        $request = Solar_Registry::get('request');

        $config = array(
            'route'      => 'blog/archives',
            'host_regex' => '([^.]*).mydomain.com',
        );
        $route = Solar::factory('Lux_Controller_Route_RegexHost', $config);

        $params = $route->match('blog/archives');
        $this->assertSame($params, false);

        // Change host to a valid one.
        $request->server['HTTP_HOST'] = 'something.mydomain.com';
        $params = $route->match('blog/archives');
        $this->assertSame($params, array());

        // Change host to a invalid one.
        $request->server['HTTP_HOST'] = 'something.anotherdomain.com';
        $params = $route->match('blog/archives');
        $this->assertSame($params, false);
    }

    public function testStaticMatchWithIgnore()
    {
        $request = Solar_Registry::get('request');

        $config = array(
            'route'       => 'blog/archives',
            'host_regex'  => '([^.]*).mydomain.com',
            'host_ignore' => '(invalid_1|invalid_2|invalid_3).mydomain.com',
        );
        $route = Solar::factory('Lux_Controller_Route_RegexHost', $config);

        $params = $route->match('blog/archives');
        $this->assertSame($params, false);

        // Change host to a valid one.
        $request->server['HTTP_HOST'] = 'something.mydomain.com';
        $params = $route->match('blog/archives');
        $this->assertSame($params, array());

        // Change host to a invalid one.
        $request->server['HTTP_HOST'] = 'something.anotherdomain.com';
        $params = $route->match('blog/archives');
        $this->assertSame($params, false);

        // Change host to a invalid *and ignored* one.
        $request->server['HTTP_HOST'] = 'invalid_1.anotherdomain.com';
        $params = $route->match('blog/archives');
        $this->assertSame($params, false);

        // Change host to a invalid *and ignored* one.
        $request->server['HTTP_HOST'] = 'invalid_2.anotherdomain.com';
        $params = $route->match('blog/archives');
        $this->assertSame($params, false);

        // Change host to a invalid *and ignored* one.
        $request->server['HTTP_HOST'] = 'invalid_3.anotherdomain.com';
        $params = $route->match('blog/archives');
        $this->assertSame($params, false);
    }

    public function testMappedMatch()
    {
        $config = array(
            // Matches 4 digits + / + 2 digits
            'route' => 'blog/archives/(\d{4})/(\d{2})',
            'map'   => array(
                1 => 'year',
                2 => 'month',
            ),
            'host_regex'  => '([^.]*).mydomain.com',
            'host_ignore' => '(invalid_1|invalid_2|invalid_3).mydomain.com',
        );
        $route = Solar::factory('Lux_Controller_Route_RegexHost', $config);

        // Set a valid hostname.
        $request = Solar_Registry::get('request');
        $request->server['HTTP_HOST'] = 'something.mydomain.com';

        $params = $route->match('blog/archives/2007/08');
        $this->assertSame(count($params), 2);
        $this->assertSame($params['year'], '2007');
        $this->assertSame($params['month'], '08');

        $params = $route->match('blog/archives/fooo/08');
        $this->assertSame($params, false);

        // Set a invalid hostname.
        $request->server['HTTP_HOST'] = 'invalid_1.mydomain.com';
        $params = $route->match('blog/archives/2007/08');
        $this->assertSame($params, false);
    }

    public function testMappedMatchWithHostMap()
    {
        $config = array(
            // Matches up to 3 paths
            'route' => 'news/(sports|politics)/([^/]*)',
            'map'   => array(
                1 => 'category',
                2 => 'title',
                3 => 'path3',
            ),
            'defaults' => array(
                'category' => null,
                'title'    => 'a_default_title',
                'path3'    => 'a_default_value',
            ),
            'host_regex'  => '([^.]*).mydomain.com',
            'host_ignore' => '(invalid_1|invalid_2|invalid_3).mydomain.com',
            'host_map'    => array(
                1 => 'category',
                2 => 'foo',
            ),
        );

        $route = Solar::factory('Lux_Controller_Route_RegexHost', $config);

        // Set a valid hostname.
        $request = Solar_Registry::get('request');
        $request->server['HTTP_HOST'] = 'something.mydomain.com';

        $params = $route->match('news/sports/article_3');
        $this->assertSame(count($params), 4);
        $this->assertSame($params, array(
            'category' => 'something',
            'title'    => 'article_3',
            'path3'    => 'a_default_value',
            'foo'      => null,
        ));
    }

    public function testAssemble()
    {
        $config = array(
            // Matches 4 digits + / + 2 digits
            'route' => 'blog/archives/(\d{4})/(\d{2})',
            'map'   => array(
                1 => 'year',
                2 => 'month',
            ),
            'host_regex'  => '([^.]*).mydomain.com',
            'host_map' => array(
                1 => 'subdomain',
            ),
            'host_ignore'  => '(invalid_1|invalid_2|invalid_3).mydomain.com',
            'host_reverse' => '%s.mydomain.com',
            'reverse'      => 'blog/archives/%s/%s',
        );
        $route = Solar::factory('Lux_Controller_Route_RegexHost', $config);

        // Set a valid hostname.
        $request = Solar_Registry::get('request');
        $request->server['HTTP_HOST'] = 'something.mydomain.com';

        $params = $route->match('blog/archives/2007/08');

        // Returned values from a matching route result in an identical uri,
        // when assembled. However with RegexHost it can also have the hostname.
        $this->assertSame($route->assemble($params),
            'http://something.mydomain.com/blog/archives/2007/08');

        // Test another subdomain, different from current HTTP_HOST.
        $params['subdomain'] = 'something_else';

        $this->assertSame($route->assemble($params),
            'http://something_else.mydomain.com/blog/archives/2007/08');
    }

    // -----------------------------------------------------------------
    //
    // Tests with *NO* host definitions
    // (simulating Lux_Controller_Route_Regex)
    //
    // -----------------------------------------------------------------

    public function testStaticMatchNoHost()
    {
        $config = array(
            'route' => 'blog/archives',
        );
        $route = Solar::factory('Lux_Controller_Route_RegexHost', $config);

        $params = $route->match('blog/archives');
        $this->assertSame($params, array());
    }

    public function testStaticMatchWithDefaultsNoHost()
    {
        $config = array(
            'route'    => 'blog/archives',
            'defaults' => array(
                'controller' => 'ctrl',
            ),
        );
        $route = Solar::factory('Lux_Controller_Route_RegexHost', $config);

        $params = $route->match('blog/archives');
        $this->assertSame(count($params), 1);
        $this->assertSame($params['controller'], 'ctrl');
    }

    public function testStaticMatchRootNoHost()
    {
        $config = array(
            'route'      => '',
        );
        $route = Solar::factory('Lux_Controller_Route_RegexHost', $config);

        $params = $route->match('/');
        $this->assertSame($params, array());
    }

    public function testStaticMatchFailureNoHost()
    {
        $config = array(
            'route' => 'blog/archives',
        );
        $route = Solar::factory('Lux_Controller_Route_RegexHost', $config);

        $params = $route->match('blog');
        $this->assertSame($params, false);
    }

    public function testMapNoHost()
    {
        $config = array(
            // Matches 4 digits + / + 2 digits
            'route' => 'blog/archives/(\d{4})/(\d{2})',
            'map'   => array(
                1 => 'year',
                2 => 'month',
            ),
        );
        $route = Solar::factory('Lux_Controller_Route_RegexHost', $config);

        $params = $route->match('blog/archives/2007/08');
        $keys = array_keys($params);
        $this->assertSame(count($keys), 2);
        $this->assertSame($keys[0], 'year');
        $this->assertSame($keys[1], 'month');
    }

    public function testMappedVariableMatchNoHost()
    {
        $config = array(
            // Matches 4 digits + / + 2 digits
            'route' => 'blog/archives/(\d{4})/(\d{2})',
            'map'   => array(
                1 => 'year',
                2 => 'month',
            ),
        );
        $route = Solar::factory('Lux_Controller_Route_RegexHost', $config);

        $params = $route->match('blog/archives/2007/08');
        $this->assertSame(count($params), 2);
        $this->assertSame($params['year'], '2007');
        $this->assertSame($params['month'], '08');

        $params = $route->match('blog/archives/fooo/08');
        $this->assertSame($params, false);
    }

    public function testOptionalMappedVariableMatchNoHost()
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
        $route = Solar::factory('Lux_Controller_Route_RegexHost', $config);

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

    public function testOptionalMappedVariableMatchWithDefaultsNoHost()
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
        $route = Solar::factory('Lux_Controller_Route_RegexHost', $config);

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