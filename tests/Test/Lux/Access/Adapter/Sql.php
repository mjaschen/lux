<?php

class Test_Lux_Access_Adapter_Sql extends Solar_Test {

    private $_access;

    public function __construct($config = array())
    {
        parent::__construct($config);

        $config = array(
            'adapter' => 'Lux_Access_Adapter_Sql',
            'config' => array(
                'table' => 'acl',
            ),
        );
        $this->_access = Solar::factory('Solar_Access', $config);

        $perms = Solar::factory('Perms');

        // flag    type    name    class               action    submit
        // allow   role    admin   Solar_App_HelloAjax action    edit
        // allow   handle  antti   Solar_App_Hello     action    edit
        // allow   role    antti   Solar_App_HelloAjax action    edit
        // allow   role    super   *                   *         *
        //
        // grant access for role 'admin' to 'Solar_App_HelloAjax'
        $data = array(
            'flag'       => 'allow',
            'type'       => 'role',
            'name'       => 'admin',
            'class_name' => 'Solar_App_HelloAjax',
            'act'        => 'action',
            'process'     => 'edit',
        );
        $perms->insert($data);

        // grant access handle 'antti' to 'Solar_App_Hello'
        $data = array(
            'flag'       => 'allow',
            'type'       => 'handle',
            'name'       => 'antti',
            'class_name' => 'Solar_App_Hello',
            'act'        => 'action',
            'process'     => 'edit',
        );
        $perms->insert($data);

        // grant access to role 'antti'
        // which is same as previous user handle
        $data = array(
            'flag'       => 'allow',
            'type'       => 'role',
            'name'       => 'antti',
            'class_name' => 'Solar_App_HelloAjax',
            'act'        => 'action',
            'process'     => 'edit',
        );
        $perms->insert($data);

        // grant role 'super' access to everything
        $data = array(
            'flag'       => 'allow',
            'type'       => 'role',
            'name'       => 'super',
            'class_name' => '*',
            'act'        => '*',
            'process'     => '*',
        );
        $perms->insert($data);
    }

    public function testFetch()
    {
        $data = array(
            array(
                'allow'  => true,
                'type'   => 'handle',
                'name'   => 'antti',
                'class'  => 'Solar_App_Hello',
                'action' => 'action',
                'process' => 'edit',
            ),
        );

        $this->_access->load('antti', array());
        $this->assertSame($this->_access->list, $data);

        $data[] = array(
            'allow'  => true,
            'type'   => 'role',
            'name'   => 'admin',
            'class'  => 'Solar_App_HelloAjax',
            'action' => 'action',
            'process' => 'edit',
        );

        // fetch by user handle 'antti' and role 'admin' and 'nobody'
        $this->_access->load('antti', array('admin', 'nobody'));
        $this->assertSame($this->_access->list, $data);

        $this->_access->load('antti', array('super'));
        $this->assertSame(count($this->_access->list), 2);

    }
}

class Perms extends Solar_Sql_Table {

    protected function _setup()
    {
        // Table name
        $this->_name = 'acl';

        // 'allow' or 'deny'
        $this->_col['flag'] = array(
            'type' => 'varchar',
            'size' => 10,
        );

        // 'role' or 'handle'
        $this->_col['type'] = array(
            'type' => 'varchar',
            'size' => 10
        );

        $this->_col['name'] = array(
            'type' => 'varchar',
            'size' => 15
        );

        $this->_col['class_name'] = array(
            'type' => 'varchar',
            'size' => 100,
        );

        $this->_col['act'] = array(
            'type' => 'varchar',
            'size' => 50,
        );

        $this->_col['process'] = array(
            'type' => 'varchar',
            'size' => 20,
        );

        // Make sure sql is available
        if (! Solar_Registry::exists('sql')) {
            Solar_Registry::set('sql', Solar::factory('Solar_Sql'));
        }
    }
}
