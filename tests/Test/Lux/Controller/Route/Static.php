<?php
// Adapted from Zend_Controller_Router_Route_StaticTest.
class Test_Lux_Controller_Route_Static extends Solar_Test
{
    public function testStaticMatch()
    {
        $config = array(
            'route' => 'users/all',
        );
        $route = Solar::factory('Lux_Controller_Route_Static', $config);

        $params = $route->match('users/all');
        $this->assertSame($params, array());
    }

    public function testStaticMatchFailure()
    {
        $config = array(
            'route' => 'archive/2006',
        );
        $route = Solar::factory('Lux_Controller_Route_Static', $config);

        $params = $route->match('users/all');
        $this->assertFalse($params);
    }

    public function testStaticMatchWithDefaults()
    {
        $config = array(
            'route'    => 'users/all',
            'defaults' => array(
                'controller' => 'ctrl',
                'action' => 'act'
            ),
        );
        $route = Solar::factory('Lux_Controller_Route_Static', $config);

        $params = $route->match('users/all');
        $this->assertSame(count($params), 2);
        $this->assertSame($params['controller'], 'ctrl');
        $this->assertSame($params['action'], 'act');
    }

    public function testStaticUTFMatch()
    {
        $config = array(
            'route' => 'żółć',
        );
        $route = Solar::factory('Lux_Controller_Route_Static', $config);

        $params = $route->match('żółć');
        $this->assertSame($params, array());
    }

    public function testRootRoute()
    {
        $config = array(
            'route' => '/',
        );
        $route = Solar::factory('Lux_Controller_Route_Static', $config);

        $params = $route->match('');
        $this->assertSame($params, array());
    }

    public function testAssemble()
    {
        $config = array(
            'route' => '/about',
        );
        $route = Solar::factory('Lux_Controller_Route_Static', $config);

        $url = $route->assemble();
        $this->assertSame($url, 'about');
    }

    public function testGetDefaults()
    {
        $config = array(
            'route'    => 'users/all',
            'defaults' => array(
                'controller' => 'ctrl',
                'action'     => 'act'
            ),
        );
        $route = Solar::factory('Lux_Controller_Route_Static', $config);

        $params = $route->getDefaults();

        $this->assertSame(count($params), 2);
        $this->assertSame($params['controller'], 'ctrl');
        $this->assertSame($params['action'], 'act');
    }

    public function testGetDefault()
    {
        $config = array(
            'route'    => 'users/all',
            'defaults' => array(
                'controller' => 'ctrl',
                'action'     => 'act'
            ),
        );
        $route = Solar::factory('Lux_Controller_Route_Static', $config);

        $this->assertSame($route->getDefault('controller'), 'ctrl');
        $this->assertSame($route->getDefault('bogus'), null);
    }
}