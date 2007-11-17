<?php
/**
 * 
 * Session adapter for sql based data store
 * 
 * @category Solar
 * 
 * @package Solar_Session
 * 
 * @author Antti Holvikari <anttih@gmail.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 */
class Solar_Session_Adapter_Sql extends Solar_Session_Adapter {
    
    /**
     * 
     * Sql object
     * 
     * @var Solar_Sql_Adapter
     * 
     */
    protected $_sql;
    
    /**
     * 
     * md5 hash of the data right after reading
     * 
     * @var string
     * 
     */
    private $_data_hash;
    
    /**
     * 
     * Configuration keys
     * 
     * Keys are:
     * 
     * `sql`
     * : (array) Configuration keys for the sql object
     * 
     * `table`
     * : (string) Table where the session data will be stored
     * 
     * `created_col`
     * : (string) Column name where time of creation is to be
     * stored
     * 
     * `sessid_col`
     * : (string) Column name of the session id
     * 
     * `data_col`
     * : (string) Column name where the actual session data will
     * be stored
     * 
     * @var array
     * 
     */
    protected $_Solar_Session_Adapter_Sql = array(
        'sql'         => null,
        'table'       => 'sessions',
        'created_col' => 'created',
        'sessid_col'  => 'sessid',
        'data_col'    => 'data',
    );
    
    /**
     * 
     * Open session handler
     * 
     * @return bool
     * 
     */
    public function open()
    {
        // register sql in the registry if not already
        if (! Solar_Registry::exists('sql')) {
            Solar_Registry::set(
                'sql',
                Solar::factory('Solar_Sql', $this->_config['sql'])
            );
        }
        
        $this->_sql = Solar_Registry::get('sql');
        return true;
    }
    
    /**
     * 
     * Reads session data
     * 
     * @return string
     * 
     */
    public function read($key)
    {
        $sel = Solar::factory('Solar_Sql_Select');
        
        $sel->from($this->_config['table'])
            ->cols($this->_config['data_col'])
            ->where("{$this->_config['sessid_col']} = ?", $key);
        
        $data = $sel->fetchValue();
        
        // Take an md5 hash of the data and remember it.
        // we will compare this with the session data
        // just before writing
        $this->_data_hash = hash('md5', $data);
        
        return $data;
    }
    
    /**
     * 
     * Writes session data
     * 
     * @return bool
     * 
     */
    public function write($key, $data)
    {
        // don't write if data is exactly the same as
        // when we read the data
        if (! empty($this->_data_hash) && hash('md5', $data) == $this->_data_hash) {
            return true;
        }
        
        $sel = Solar::factory('Solar_Sql_Select');
        
        $sel->from($this->_config['table'])
            ->cols($this->_config['sessid_col'])
            ->where("{$this->_config['sessid_col']} = ?", $key);
        
        $rows = $sel->countPages($this->_config['sessid_col']);
        
        if ($rows['count'] == 1) {
            try {
                // there is already data for this sessid
                // so do update only
                $this->_sql->update(
                    $this->_config['table'],
                    array($this->_config['data_col'] => $data),
                    array("{$this->_config['sessid_col']} = ?" => $key)
                );
            } catch (Solar_Sql_Exception $e) {
                // don't throw, just return false
                return false;
            }
            
        } else {
            try {
                // insert
                $this->_sql->insert($this->_config['table'], array(
                    $this->_config['created_col'] => date('m-d-Y H:i:s'),
                    $this->_config['sessid_col']  => $key,
                    $this->_config['data_col']    => $data,
                ));
            } catch (Solar_Sql_Exception $e) {
                // don't throw, just return false
                return false;
            }
        }
        
        return true;
    }
    
    /**
     * 
     * Destroys session data
     * 
     * @return bool
     * 
     */
    public function destroy($key)
    {
        // delete
        $this->_sql->delete(
            $this->_config['table'],
            array("{$this->_config['sessid_col']} = ?" => $key)
        );
        
        return true;
    }
    
    /**
     * 
     * Deletes old session data
     * 
     * @param int $lifetime Session data older than this
     * will be removed. This value can be set using the
     * `session.gc_lifetime` ini` setting.
     
     * @return bool
     * 
     */
    public function gc($lifetime)
    {
        // timestamp is current time minus session.gc_maxlifetime
        $timestamp = date(
            'm-d-Y H:i:s',
            mktime(date('H'), date('i'), date('s') - $lifetime)
        );
        
        // delete all sessions created before the timestamp
        $this->_sql->delete($this->_config['table'], array(
            "{$this->_config['created_col']} < ?" => $timestamp,
        ));
        
        return true;
    }
}