<?php
// Adapted from Zend_Controller_Router_RouteTest.
class Test_Lux_Controller_Route_Route extends Solar_Test
{
    public function testStaticMatch()
    {
        $config = array(
            'route' => 'users/all',
        );
        $route = Solar::factory('Lux_Controller_Route_Route', $config);

        $values = $route->match('users/all');
        $this->assertSame($values, array());
    }

    public function testStaticUTFMatch()
    {
        $config = array(
            'route' => 'żółć',
        );
        $route = Solar::factory('Lux_Controller_Route_Route', $config);

        $values = $route->match('żółć');
        $this->assertSame($values, array());
    }

    public function testURLDecode()
    {
        $config = array(
            'route' => 'żółć',
        );
        $route = Solar::factory('Lux_Controller_Route_Route', $config);
        $values = $route->match('%C5%BC%C3%B3%C5%82%C4%87');

        $this->assertSame($values, array());
    }

    public function testStaticPathShorterThanParts()
    {
        $config = array(
            'route' => 'users/a/martel',
        );
        $route = Solar::factory('Lux_Controller_Route_Route', $config);

        $values = $route->match('users/a');
        $this->assertSame($values, false);
    }

    public function testStaticPathLongerThanParts()
    {
        $config = array(
            'route' => 'users/a',
        );
        $route = Solar::factory('Lux_Controller_Route_Route', $config);

        $values = $route->match('users/a/martel');
        $this->assertSame($values, false);
    }

    public function testStaticMatchWithDefaults()
    {
        $config = array(
            'route'    => 'users/all',
            'defaults' => array(
                'controller' => 'ctrl'
            ),
        );
        $route = Solar::factory('Lux_Controller_Route_Route', $config);

        $values = $route->match('users/all');
        $this->assertSame($values['controller'], 'ctrl');
    }

    public function testNotMatched()
    {
        $config = array(
            'route' => 'users/all',
        );
        $route = Solar::factory('Lux_Controller_Route_Route', $config);

        $values = $route->match('users/martel');
        $this->assertSame($values, false);
    }

    public function testNotMatchedWithVariablesAndDefaults()
    {
        $config = array(
            'route'    => ':controller/:action',
            'defaults' => array(
                'controller' => 'index',
                'action' => 'index'
            ),
        );
        $route = Solar::factory('Lux_Controller_Route_Route', $config);

        $values = $route->match('archive/action/bogus');
        $this->assertSame($values, false);
    }


    public function testNotMatchedWithVariablesAndStatic()
    {
        $config = array(
            'route' => 'archive/:year/:month',
        );
        $route = Solar::factory('Lux_Controller_Route_Route', $config);

        $values = $route->match('ctrl/act/2000');
        $this->assertSame($values, false);
    }

    public function testStaticMatchWithWildcard()
    {
        $config = array(
            'route'    => 'news/view/*',
            'defaults' => array(
                'controller' => 'news',
                'action' => 'view'
            ),
        );
        $route = Solar::factory('Lux_Controller_Route_Route', $config);

        $values = $route->match('news/view/show/all/year/2000/empty');
        $this->assertEquals('news', $values['controller']);
        $this->assertEquals('view', $values['action']);
        $this->assertEquals('all', $values['show']);
        $this->assertEquals('2000', $values['year']);
        $this->assertEquals(null, $values['empty']);
    }

    public function testWildcardWithUTF()
    {
        $config = array(
            'route'    => 'news/*',
            'defaults' => array(
                'controller' => 'news',
                'action' => 'view'
            ),
        );
        $route = Solar::factory('Lux_Controller_Route_Route', $config);

        $values = $route->match('news/klucz/wartość/wskaźnik/wartość');
        $this->assertEquals('news', $values['controller']);
        $this->assertEquals('view', $values['action']);
        $this->assertEquals('wartość', $values['klucz']);
        $this->assertEquals('wartość', $values['wskaźnik']);
    }

    public function testWildcardURLDecode()
    {
        $config = array(
            'route'    => 'news/*',
            'defaults' => array(
                'controller' => 'news',
                'action' => 'view'
            ),
        );
        $route = Solar::factory('Lux_Controller_Route_Route', $config);

        $values = $route->match('news/wska%C5%BAnik/warto%C5%9B%C4%87');
        $this->assertEquals('news', $values['controller']);
        $this->assertEquals('view', $values['action']);
        $this->assertEquals('wartość', $values['wskaźnik']);
    }

    public function testVariableValues()
    {
        $config = array(
            'route' => ':controller/:action/:year',
        );
        $route = Solar::factory('Lux_Controller_Route_Route', $config);

        $values = $route->match('ctrl/act/2000');
        $this->assertSame($values['controller'], 'ctrl');
        $this->assertSame($values['action'], 'act');
        $this->assertSame($values['year'], '2000');
    }

    public function testVariableUTFValues()
    {
        $config = array(
            'route' => 'test/:param',
        );
        $route = Solar::factory('Lux_Controller_Route_Route', $config);

        $values = $route->match('test/aä');
        $this->assertSame($values['param'], 'aä');
    }

    public function testOneVariableValue()
    {
        $config = array(
            'route'    => ':action',
            'defaults' => array(
                'controller' => 'ctrl',
                'action' => 'action'
            ),
        );
        $route = Solar::factory('Lux_Controller_Route_Route', $config);

        $values = $route->match('act');
        $this->assertSame($values['controller'], 'ctrl');
        $this->assertSame($values['action'], 'act');
    }

    public function testVariablesWithDefault()
    {
        $config = array(
            'route'    => ':controller/:action/:year',
            'defaults' => array(
                'year' => '2006'
            ),
        );
        $route = Solar::factory('Lux_Controller_Route_Route', $config);

        $values = $route->match('ctrl/act');
        $this->assertSame($values['controller'], 'ctrl');
        $this->assertSame($values['action'], 'act');
        $this->assertSame($values['year'], '2006');
    }

    public function testVariablesWithNullDefault() // Kevin McArthur
    {
        $config = array(
            'route'    => ':controller/:action/:year',
            'defaults' => array(
                'year' => null
            ),
        );
        $route = Solar::factory('Lux_Controller_Route_Route', $config);

        $values = $route->match('ctrl/act');
        $this->assertSame($values['controller'], 'ctrl');
        $this->assertSame($values['action'], 'act');
        $this->assertNull($values['year']);
    }

    public function testVariablesWithDefaultAndValue()
    {
        $config = array(
            'route'    => ':controller/:action/:year',
            'defaults' => array(
                'year' => '2006'
            ),
        );
        $route = Solar::factory('Lux_Controller_Route_Route', $config);
        $values = $route->match('ctrl/act/2000');

        $this->assertSame($values['controller'], 'ctrl');
        $this->assertSame($values['action'], 'act');
        $this->assertSame($values['year'], '2000');
    }

    public function testVariablesWithRequirementAndValue()
    {
        $config = array(
            'route' => ':controller/:action/:year',
            'reqs'  => array(
                'year' => '\d+'
            ),
        );
        $route = Solar::factory('Lux_Controller_Route_Route', $config);

        $values = $route->match('ctrl/act/2000');
        $this->assertSame($values['controller'], 'ctrl');
        $this->assertSame($values['action'], 'act');
        $this->assertSame($values['year'], '2000');
    }

    public function testVariablesWithRequirementAndIncorrectValue()
    {
        $config = array(
            'route' => ':controller/:action/:year',
            'reqs'  => array(
                'year' => '\d+'
            ),
        );
        $route = Solar::factory('Lux_Controller_Route_Route', $config);

        $values = $route->match('ctrl/act/2000t');
        $this->assertSame($values, false);
    }

    public function testVariablesWithDefaultAndRequirement()
    {
        $config = array(
            'route'    => ':controller/:action/:year',
            'defaults' => array(
                'year' => '2006'
            ),
            'reqs'     => array(
                'year' => '\d+'
            ),
        );
        $route = Solar::factory('Lux_Controller_Route_Route', $config);

        $values = $route->match('ctrl/act/2000');
        $this->assertSame($values['controller'], 'ctrl');
        $this->assertSame($values['action'], 'act');
        $this->assertSame($values['year'], '2000');
    }

    public function testVariablesWithDefaultAndRequirementAndIncorrectValue()
    {
        $config = array(
            'route'    => ':controller/:action/:year',
            'defaults' => array(
                'year' => '2006'
            ),
            'reqs'     => array(
                'year' => '\d+'
            ),
        );
        $route = Solar::factory('Lux_Controller_Route_Route', $config);

        $values = $route->match('ctrl/act/2000t');
        $this->assertSame($values, false);
    }

    public function testVariablesWithDefaultAndRequirementAndWithoutValue()
    {
        $config = array(
            'route'    => ':controller/:action/:year',
            'defaults' => array(
                'year' => '2006'
            ),
            'reqs'     => array(
                'year' => '\d+'
            ),
        );
        $route = Solar::factory('Lux_Controller_Route_Route', $config);

        $values = $route->match('ctrl/act');
        $this->assertSame($values['controller'], 'ctrl');
        $this->assertSame($values['action'], 'act');
        $this->assertSame($values['year'], '2006');
    }

    public function testVariablesWithWildcardAndNumericKey()
    {
        $config = array(
            'route' => ':controller/:action/:next/*',
        );
        $route = Solar::factory('Lux_Controller_Route_Route', $config);

        $values = $route->match('c/a/next/2000/show/all/sort/name');
        $this->assertSame($values['controller'], 'c');
        $this->assertSame($values['action'], 'a');
        $this->assertSame($values['next'], 'next');
        $this->assertTrue(array_key_exists('2000', $values));
    }

    public function testRootRoute()
    {
        $config = array(
            'route' => '/',
        );
        $route = Solar::factory('Lux_Controller_Route_Route', $config);

        $values = $route->match('');
        $this->assertSame($values, array());
    }

    public function testAssemble()
    {
        $config = array(
            'route' => 'authors/:name',
        );
        $route = Solar::factory('Lux_Controller_Route_Route', $config);

        $url = $route->assemble(array('name' => 'martel'));
        $this->assertSame($url, 'authors/martel');
    }

    public function testAssembleWithoutValue()
    {
        $config = array(
            'route' => 'authors/:name',
        );
        $route = Solar::factory('Lux_Controller_Route_Route', $config);

        try {
            $url = $route->assemble();
        } catch (Exception $e) {
            return $this->assertTrue(true);
        }

        $this->fail();
    }

    public function testAssembleWithDefault()
    {
        $config = array(
            'route'    => 'authors/:name',
            'defaults' => array(
                'name' => 'martel'
            ),
        );
        $route = Solar::factory('Lux_Controller_Route_Route', $config);

        $url = $route->assemble();
        $this->assertSame($url, 'authors');
    }

    public function testAssembleWithDefaultAndValue()
    {
        $config = array(
            'route'    => 'authors/:name',
            'defaults' => array(
                'name' => 'martel'
            ),
        );
        $route = Solar::factory('Lux_Controller_Route_Route', $config);

        $url = $route->assemble(array('name' => 'mike'));
        $this->assertSame($url, 'authors/mike');
    }

    public function testAssembleWithWildcardMap()
    {
        $config = array(
            'route' => 'authors/:name/*',
        );
        $route = Solar::factory('Lux_Controller_Route_Route', $config);

        $url = $route->assemble(array('name' => 'martel'));
        $this->assertSame($url, 'authors/martel');
    }

    public function testAssembleWithReset()
    {
        $config = array(
            'route'    => 'archive/:year/*',
            'defaults' => array(
                'controller' => 'archive',
                'action' => 'show'
            ),
        );
        $route = Solar::factory('Lux_Controller_Route_Route', $config);

        $values = $route->match('archive/2006/show/all/sort/name');
        $url = $route->assemble(array('year' => '2005'), true);
        $this->assertSame($url, 'archive/2005');
    }

    public function testAssembleWithReset2()
    {
        $config = array(
            'route'    => ':controller/:action/*',
            'defaults' => array(
                'controller' => 'archive',
                'action' => 'show'
            ),
        );
        $route = Solar::factory('Lux_Controller_Route_Route', $config);

        $values = $route->match('users/list');
        $url = $route->assemble(array(), true);
        $this->assertSame($url, '');
    }

    public function testAssembleWithReset3()
    {
        $config = array(
            'route'    => 'archive/:year/*',
            'defaults' => array(
                'controller' => 'archive',
                'action'     => 'show',
                'year' => 2005
            ),
        );
        $route = Solar::factory('Lux_Controller_Route_Route', $config);

        $values = $route->match('archive/2006/show/all/sort/name');
        $url = $route->assemble(array(), true);
        $this->assertSame($url, 'archive');
    }

    public function testAssembleWithReset4()
    {
        $config = array(
            'route'    => ':controller/:action/*',
            'defaults' => array(
                'controller' => 'archive',
                'action' => 'show'
            ),
        );
        $route = Solar::factory('Lux_Controller_Route_Route', $config);

        $values = $route->match('users/list');
        $url = $route->assemble(array('action' => 'display'), true);
        $this->assertSame($url, 'archive/display');
    }

    public function testAssembleWithReset5()
    {
        $config = array(
            'route'    => '*',
            'defaults' => array(
                'controller' => 'index',
                'action' => 'index'
            ),
        );
        $route = Solar::factory('Lux_Controller_Route_Route', $config);

        $values = $route->match('key1/value1/key2/value2');
        $url = $route->assemble(array('key1' => 'newvalue'), true);
        $this->assertSame($url, 'key1/newvalue');
    }

    public function testAssembleWithWildcardAndAdditionalParameters()
    {
        $config = array(
            'route' => 'authors/:name/*',
        );
        $route = Solar::factory('Lux_Controller_Route_Route', $config);

        $url = $route->assemble(array('name' => 'martel', 'var' => 'value'));
        $this->assertSame($url, 'authors/martel/var/value');
    }

    public function testAssembleWithUrlVariablesReuse()
    {
        $config = array(
            'route' => 'archives/:year/:month',
        );
        $route = Solar::factory('Lux_Controller_Route_Route', $config);

        $values = $route->match('archives/2006/07');
        $this->assertTrue(is_array($values));

        $url = $route->assemble(array('month' => '03'));
        $this->assertSame($url, 'archives/2006/03');
    }

    public function testWildcardUrlVariablesOverwriting()
    {
        $config = array(
            'route'    => 'archives/:year/:month/*',
            'defaults' => array(
                'controller' => 'archive'
            ),
        );
        $route = Solar::factory('Lux_Controller_Route_Route', $config);

        $values = $route->match('archives/2006/07/controller/test/year/10000/sort/author');
        $this->assertTrue(is_array($values));
        $this->assertSame($values['controller'], 'archive');
        $this->assertSame($values['year'], '2006');
        $this->assertSame($values['month'], '07');
        $this->assertSame($values['sort'], 'author');
    }

    public function testGetDefaults()
    {
        $config = array(
            'route'    => 'users/all',
            'defaults' => array(
                'controller' => 'ctrl',
                'action' => 'act'
            ),
        );
        $route = Solar::factory('Lux_Controller_Route_Route', $config);

        $values = $route->getDefaults();
        $this->assertTrue(is_array($values));
        $this->assertSame($values['controller'], 'ctrl');
        $this->assertSame($values['action'], 'act');
    }

    public function testGetDefault()
    {
        $config = array(
            'route'    => 'users/all',
            'defaults' => array(
                'controller' => 'ctrl',
                'action' => 'act'
            ),
        );
        $route = Solar::factory('Lux_Controller_Route_Route', $config);

        $this->assertSame($route->getDefault('controller'), 'ctrl');
        $this->assertSame($route->getDefault('bogus'), null);
    }

    public function testAssembleResetDefaults()
    {
        $config = array(
            'route'    => ':controller/:action/*',
            'defaults' => array(
                'controller' => 'index',
                'action' => 'index'
            ),
        );
        $route = Solar::factory('Lux_Controller_Route_Route', $config);

        $values = $route->match('news/view/id/3');
        $url = $route->assemble(array('action' => null));
        $this->assertSame($url, 'news/index/id/3');

        $url = $route->assemble(array('action' => null, 'id' => null));
        $this->assertSame($url, 'news');
    }

    public function testAssembleWithRemovedDefaults() // Test for ZF-1197
    {
        $config = array(
            'route'    => ':controller/:action/*',
            'defaults' => array(
                'controller' => 'index',
                'action' => 'index'
            ),
        );
        $route = Solar::factory('Lux_Controller_Route_Route', $config);

        $url = $route->assemble(array('id' => 3));
        $this->assertSame($url, 'index/index/id/3');

        $url = $route->assemble(array('action' => 'test'));
        $this->assertSame($url, 'index/test');

        $url = $route->assemble(array('action' => 'test', 'id' => 3));
        $this->assertSame($url, 'index/test/id/3');

        $url = $route->assemble(array('controller' => 'test'));
        $this->assertSame($url, 'test');

        $url = $route->assemble(array('controller' => 'test', 'action' => 'test'));
        $this->assertSame($url, 'test/test');

        $url = $route->assemble(array('controller' => 'test', 'id' => 3));
        $this->assertSame($url, 'test/index/id/3');

        $url = $route->assemble(array());
        $this->assertSame($url, '');

        $route->match('ctrl');

        $url = $route->assemble(array('id' => 3));
        $this->assertSame($url, 'ctrl/index/id/3');

        $url = $route->assemble(array('action' => 'test'));
        $this->assertSame($url, 'ctrl/test');

        $url = $route->assemble();
        $this->assertSame($url, 'ctrl');

        $route->match('index');

        $url = $route->assemble();
        $this->assertSame($url, '');
    }
}