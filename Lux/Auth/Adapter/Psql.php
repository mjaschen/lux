<?php
/**
 * 
 * Persistent authentication using cookies and a SQL database table.
 * 
 * Follows Chris Shiflett's recommendations in "Essential PHP Security" -
 * chapter 7.4. Persistent Logins. You can decide if you want a user
 * to be remembered **once**, or forever as long as they re-authenticate
 * within a certain time-window.
 * 
 * @category Lux
 * 
 * @package Lux_Auth
 * 
 * @author Antti Holvikari <anttih@gmail.com>
 * 
 * @author Rodrigo Moraes <rodrigo.moraes@gmail.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id$
 * 
 */
class Lux_Auth_Adapter_Psql extends Solar_Auth_Adapter_Sql
{
    /**
     *
     * User-provided configuration values. Keys are...
     * 
     * `token_table`
     * : (string) Name of the table holding cookie information
     * 
     * `token_handle_col`
     * : (string) Name of the column with user handle
     * 
     * `token_identifier_col`
     * : (string) Name of the column with identifier
     * 
     * `token_token_col`
     * : (string) Name of the column with a secret token
     * 
     * `token_timeout_col`
     * : (string) Name of the column with timeout timestamps (time())
     * 
     * `source_persist`
     * : (string) Persist key in the credential array source, default 'persist'.
     * 
     * `timeout`
     * : (int) Time in seconds in which time user's cookie is valid
     *   for authentication. This value is used for the server-side
     *   timestamp as well as for the cookie itself. Timestamp will be
     *   created by adding this value to time(). Default is 86400 (one day).
     * 
     * `hash_algo`
     * : (string) Name of the hashing algorithm (i.e. 'md5', 'sha256',
     *   'haval160,4' etc.). Default is 'md5'.
     * 
     * `cookie_name`
     * : (string) Name of the cookie.
     * 
     * `cookie_path`
     * : (string) Path option for setcookie().
     * 
     * `cookie_domain`
     * : (string) Domain option for setcookie().
     * 
     * `cookie_secure`
     * : (bool) Secure option for setcookie().
     * 
     * `cookie_httponly`
     * : (bool) setcookie() option: When TRUE the cookie will be made accessible
     *   only through the HTTP protocol.
     * 
     * @var array
     *
     */
    protected $_Lux_Auth_Adapter_Psql = array(
        'token_table'          => 'cookies',
        'token_handle_col'     => 'handle',
        'token_identifier_col' => 'identifier',
        'token_token_col'      => 'token',
        'token_timeout_col'    => 'timeout',
        'source_persist'       => 'persist',
        'timeout'              => 86400,
        'hash_algo'            => 'md5',
        'cookie_name'          => 'auth',
        'cookie_path'          => '/',
        'cookie_domain'        => '',
        'cookie_secure'        => false,
        'cookie_httponly'      => true,
    );
    
    /**
     * 
     * Solar_Sql_Adapter instance
     * 
     * @var Solar_Sql_Adapter
     * 
     */
    protected $_sql;
    
    /**
     * Undocumented function
     *
     * @return void
     */
    public function __construct($config = null)
    {
        parent::__construct($config);
        
        // get the dependency object of class Solar_Sql
        $this->_sql = Solar::dependency('Solar_Sql', $this->_config['sql']);
    }
    /**
     * 
     * Starts a session with authentication.
     * 
     * @return void
     * 
     */
    public function start()
    {
        // load the session
        $this->_loadSession();
        
        // update idle and expire times no matter what
        $this->updateIdleExpire();
        
        // if current auth **is not** valid, and processing is allowed,
        // process login attempts
        if (! $this->isValid() && $this->allow && $this->isLoginRequest()) {
            $this->processLogin();
            if ($this->isValid()) {
                // was a valid login, attempt to redirect.
                $this->_redirect();
            }
        }
        
        // if auth it not valid, processing is allowed and this is not
        // a login attempt, try to login with cookie
        if (! $this->isValid() && $this->allow && ! $this->isLoginRequest()) {
            $this->_processCookieLogin();
        }
        
        // if current auth **is** valid, and processing is allowed,
        // process logout attempts, and redirect if requested.
        if ($this->isValid() && $this->allow && $this->isLogoutRequest()) {
            $this->processLogout();
            $this->_redirect();
        }
    }
    
    /**
     *
     * Verifies an username handle and password.
     *
     * @return mixed An array of verified user information, or boolean false
     * if verification failed.
     *
     */
    protected function _processLogin()
    {
        $info = parent::_processLogin();
        
        // was this a successful login?
        if ($info) {
            // check to see if user wants persistent auth
            $method = strtolower($this->_config['source']);
            $persist = (bool) $this->_request->$method(
                $this->_config['source_persist'],
                false
            );
            
            if ($persist) {
                $handle = $info[$this->_config['handle_col']];
                $this->_newCookie($handle);
            }
        }
        
        return $info;
    }
    
    /**
     *
     * Processes a cookie login attempt and sets user credentials.
     *
     * @return bool True if the login was successful, false if not.
     *
     */
    protected function _processCookieLogin()
    {
        // get cookie
        $cookie = $this->_request->cookie($this->_config['cookie_name'], false);
        
        if ($cookie) {
            // parse cookie
            list($identifier, $token) = explode(':', $cookie);
            
            // sanity check
            if (empty($identifier) || empty($token)) {
                return false;
            }
            
            // get a selection tool using the dependency object
            $select = Solar::factory(
                'Solar_Sql_Select',
                array('sql' => $this->_sql)
            );
            
            $identifier_col = $this->_config['token_identifier_col'];
            $token_col      = $this->_config['token_token_col'];
            $timeout_col    = $this->_config['token_timeout_col'];
            
            // build select
            $select->from($this->_config['token_table'])
                   ->cols(array($this->_config['token_handle_col']))
                   ->multiWhere(array(
                       "$identifier_col = ?" => $identifier,
                       "$token_col = ?"      => $token,
                       "$timeout_col > ?"    => time(),
                   ));
            
            // fetch one row
            $token_found = $select->fetch('one');
            
            if ($token_found) {
                
                // now we need to fetch info from auth table
                
                $cols = array();
                
                // always fetch the handle
                $cols[] = $this->_config['handle_col'];
                
                // list of optional columns as (property => field)
                $optional = array(
                    'email'   => 'email_col',
                    'moniker' => 'moniker_col',
                    'uri'     => 'uri_col',
                    'uid'     => 'uid_col',
                );
                
                // get optional columns
                foreach ($optional as $key => $val) {
                    if ($this->_config[$val]) {
                        $cols[] = $this->_config[$val];
                    }
                }
                
                // use user handle from the token table
                $handle = $token_found[$this->_config['token_handle_col']];
                
                // get a selection tool using the dependency object
                $select = Solar::factory(
                    'Solar_Sql_Select',
                    array('sql' => $this->_sql)
                );
                
                // build the select
                $select->from($this->_config['table'])
                       ->cols($cols)
                       ->where("{$this->_config['handle_col']} = ?", $handle)
                       ->multiWhere($this->_config['where']);
                
                // fetch all
               $data = $select->fetch('all');
                
                // user that used a cookie was found in the real auth table.
                // fail authentication!
                if (count($data) != 1) {
                    return false;
                }
                
                // remove old token
                $this->_deleteToken($token);
                
                // make a new token and set the cookie
                $this->_newCookie($handle);
                
                // successful login, treat result as user info
                $this->reset('VALID', $data);
                return true;
            }
        }
        
        // fail in every other case
        return false;
    }
    
    /**
     *
     * Creates and saves a new token in the database and sets/renews a cookie.
     *
     * @param string $handle User handle
     *
     */
    protected function _newCookie($handle)
    {
        // generate identifier
        $identifier = $this->_createIdentifier($handle);
        
        // generate a secret token
        $token = hash($this->_config['hash_algo'], uniqid(rand(), true));
        
        // set timeout timestamp for the token (in seconds)
        $timeout = time() + $this->_config['timeout'];
        
        // data to be updated
        $data = array(
            $this->_config['token_handle_col']     => $handle,
            $this->_config['token_identifier_col'] => $identifier,
            $this->_config['token_token_col']      => $token,
            $this->_config['token_timeout_col']    => $timeout,
        );
        
        $this->_sql->insert($this->_config['token_table'], $data);
        
        // finally, set the cookie
        $this->_setCookie(
            "$identifier:$token",
            time() + $this->_config['timeout']
        );
    }
    
    /**
     * 
     * Deletes one token row from table
     * 
     * @param string $token Token
     * 
     * @return void
     * 
     */
    protected function _deleteToken($token)
    {
        // just perform a DELETE
        $this->_sql->delete(
            $this->_config['token_table'],
            array("{$this->_config['token_token_col']} = ?" => $token)
        );
    }
    
    /**
     *
     * Adapter-specific logout processing.
     *
     * @return string A status code string for reset().
     *
     */
    protected function _processLogout()
    {
        // first, log us out
        $status = parent::_processLogout();
        
        // delete auth cookie
        $this->_setCookie('DELETED', time());
        
        // return status from parent
        return $status;
    }
    
    /**
     * 
     * Sets a cookie
     * 
     * @param string $content Cookie content
     * 
     * @param int $expire Expire time in seconds
     * 
     * @return void
     * 
     */
    protected function _setCookie($content, $expire)
    {
        // set cookie
        setcookie(
            $this->_config['cookie_name'],
            $content,
            $expire,
            $this->_config['cookie_path'],
            $this->_config['cookie_domain'],
            $this->_config['cookie_secure'],
            $this->_config['cookie_httponly']
        );
    }
    
    /**
     * 
     * Creates the identifier for a user handle
     * 
     * @param string $handle User handle
     * 
     * @return void
     * 
     */
    protected function _createIdentifier($handle)
    {
        // generate identifier
        $identifier = hash(
            $this->_config['hash_algo'],
            $this->_config['salt']
            . hash($this->_config['hash_algo'], $handle)
        );
        
        return $identifier;
    }
}