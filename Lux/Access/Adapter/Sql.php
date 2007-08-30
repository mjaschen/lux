<?php
/**
 * 
 * Class for reading access privileges from a database table.
 * 
 * @category Lux
 * 
 * @package Lux_Access
 * 
 * @author Antti Holvikari <anttih@gmail.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 */

/**
 * 
 * Class for reading access privileges from a database table.
 * 
 *     0:flag 1:type 2:name 3:class 4:action 5:process
 * 
 * @category Lux
 * 
 * @package Lux_Access
 * 
 */
class Lux_Access_Adapter_Sql extends Solar_Access_Adapter {
    
    /**
     * 
     * Config keys
     *
     * `sql`
     * : (string|array) How to get the SQL object.  If a string, is
     *   treated as a [[Solar::registry()]] object name.  If array, treated as
     *   config for a standalone Solar_Sql object.
     *
     * `table`
     * : (string) Name of the table holding access data.
     * 
     * `flag_col`
     * : (string) Name of the column with privilige flag.
     *   Either 'allow' or 'deny'.
     * 
     * `type_col`
     * : (string) Name of the column with access type info.
     *   Either 'handle' or 'role'.
     * 
     * `name_col`
     * : (string) Name of the column with the handle or role name.
     * 
     * `class_col`
     * : (string) Name of the column with the class name.
     * 
     * `action_col`
     * : (string) Name of the column with the action name.
     * 
     * `process_col`
     * : (string) Name of the column with the submit key name.
     * 
     * @var array
     * 
     */
    protected $_Lux_Access_Adapter_Sql = array(
        'sql'         => 'sql',
        'table'       => null,
        'flag_col'    => 'flag',
        'type_col'    => 'type',
        'name_col'    => 'name',
        'class_col'   => 'class_name',
        'action_col'  => 'act',
        'process_col' => 'process',
    );
    
    /**
     * 
     * Fetch access privileges for a user handle and roles.
     * 
     * @param string $handle User handle.
     * 
     * @param array $roles User roles.
     * 
     * @return array
     * 
     */
    public function fetch($handle, $roles)
    {
        // get the dependency object of class Solar_Sql
        $sql = Solar::dependency('Solar_Sql', $this->_config['sql']);
        
        // get a selection tool using the dependency object
        $select = Solar::factory(
            'Solar_Sql_Select',
            array('sql' => $sql)
        );
        
        // columns to select
        $columns = array(
            $this->_config['flag_col']    . ' AS allow',
            $this->_config['type_col']    . ' AS type',
            $this->_config['name_col']    . ' AS name',
            $this->_config['class_col']   . ' AS class',
            $this->_config['action_col']  . ' AS action',
            $this->_config['process_col'] . ' AS process',
        );
        
        // FROM
        $select->from($this->_config['table'], $columns);
        
        // We need nested AND because type must be 'handle'
        // *and* handle must be one of provided (including '*') 
        // at the same time.
        // 
        // We need:
        // (:type_col = 'handle' AND :name_col IN(?, '*')
        
        $where  = "({$this->_config['type_col']} = 'handle'";
        $where .= " AND {$this->_config['name_col']}";
        $where .= " IN(?))";
        
        // quote handle name into SQL
        $where = $select->quoteInto($where, array($handle, '*'));
        
        // add where
        $select->where($where);
        
        // force to array
        settype($roles, 'array');
        
        if (! empty($roles)) {
            
            // We need nested AND because type must be 'role' *and* role 
            // must be one of provided (including '*') at the same time.
            //
            // We need:
            // OR (:type_col = 'role' AND :name_col IN('role0', 'role1', '*'))
            
            // add wild card search to roles
            $roles[] = '*';
            
            $where  = "({$this->_config['type_col']} = 'role'";
            $where .= " AND {$this->_config['name_col']}";
            $where .= " IN(?))";
            
            // quote roles into the SQL
            $where = $select->quoteInto($where, $roles);
            
            // add where using OR
            $select->orWhere($where);
        }
        
        // fetch and cast to array
        $list = $select->fetch('all');
        
        for ($i = 0; $i < count($list); $i++) {
            // set 'allow' flag to boolean
            $list[$i]['allow'] = ($list[$i]['allow'] == 'allow') ? true : false;
        }
        
        // return access list
        return $list;
    }
}
