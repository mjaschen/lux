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

    public function testMappedVariableMatch()
    {
        $config = array(
            'route' => 'blog/archives/(.+)',
            'map'   => array(
                1 => 'path_1'
            ),
        );
        $route = Solar::factory('Lux_Controller_Route_Regex', $config);

        $values = $route->match('blog/archives/something');

        $this->assertSame(1, count($values));
        $this->assertSame('something', $values['path_1']);

        $values = array_keys($values);
        $this->assertSame('path_1', $values[0]);
    }
}