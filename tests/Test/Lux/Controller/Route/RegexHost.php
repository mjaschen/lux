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

        $this->assertSame($route->assemble($params),
            'http://something.mydomain.com/blog/archives/2007/08');
    }
}