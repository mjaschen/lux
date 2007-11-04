<?php
$config = array();

$config['Solar'] = array(
    'ini_set' => array(
        'error_reporting' => (E_ALL|E_STRICT),
        'display_errors'  => true,
        'html_errors'     => true,
        'date.timezone'   => 'America/Chicago',
    ),
);

$config['Solar_Debug_Var']['output'] = 'text';

$config['Solar_Sql'] = array(
    'adapter' => 'Solar_Sql_Adapter_Sqlite',
);

$config['Solar_Sql_Adapter_Sqlite'] = array(
    'name'   => ':memory:',
);

$config['Solar_Sql_Adapter_Mysql'] = array(
    'name'   => 'database',
    'user'   => 'root',
    'passwd' => '',
);

$config['Solar_Cache_Adapter_File'] = array(
    'path' => '/tmp/',
);

return $config;
?>