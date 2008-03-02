<?php
/**
 * 
 * Persistent authentication using cookies and a SQL database table.
 * 
 * Follows Chris Shiflett's recommendations in "Essential PHP Security" -
 * chapter 7.4. Persistent Logins.
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
     * `identifier_col`
     * : (string) Name of the column with identifier
     * 
     * `token_col`
     * : (string) Name of the column with a secret token
     * 
     * `timeout_col`
     * : (string) Name of the column with timeout timestamps (time())
     * 
     * `source_persist`
     * : (string) Persist key in the credential array source, default 'persist'.
     * 
     * `timeout`
     * : (int) Token lifetime in seconds. After this time, it is reset and
     *   saved again in the database. Default is 86400 (one day).
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
        'identifier_col'  => 'identifier',
        'token_col'       => 'token',
        'timeout_col'     => 'timeout',
        'source_persist'  => 'persist',
        'timeout'         => 604800,
        'hash_algo'       => 'md5',
        'cookie_name'     => 'auth',
        'cookie_path'     => '/',
        'cookie_domain'   => '',
        'cookie_secure'   => false,
        'cookie_httponly' => true,
    );
    
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
            
            // cookie
            list($identifier, $token) = explode(':', $cookie);
            
            // get the dependency object of class Solar_Sql
            $obj = Solar::dependency('Solar_Sql', $this->_config['sql']);
            
            // get a selection tool using the dependency object
            $select = Solar::factory(
                'Solar_Sql_Select',
                array('sql' => $obj)
            );
            
            // list of optional columns as (property => field)
            $optional = array(
                'email'   => 'email_col',
                'moniker' => 'moniker_col',
                'uri'     => 'uri_col',
                'uid'     => 'uid_col',
            );
            
            // always get these columns
            $cols = array(
                $this->_config['handle_col'],
                $this->_config['identifier_col'],
                $this->_config['token_col'],
                $this->_config['timeout_col'],
            );
            
            // get optional columns
            foreach ($optional as $key => $val) {
                if ($this->_config[$val]) {
                    $cols[] = $this->_config[$val];
                }
            }
            
            // build select
            $select->from($this->_config['table'])
                   ->cols($cols)
                   ->where("{$this->_config['identifier_col']} = ?", $identifier);
            
            // fetch one row
            $data = $select->fetch('one');
            
            if ($data) {
                
                $identifier_real = hash(
                    $this->_config['hash_algo'],
                    $this->_config['salt']
                    . hash($this->_config['hash_algo'], $data[$this->_config['handle_col']])
                );
                
                $token_check      = $data[$this->_config['token_col']] == $token;
                $identifier_check = $data[$this->_config['identifier_col']] == $identifier_real;
                
                // if any of these fail we'll just return false
                if (! $token_check || ! $identifier_check) {
                    return false;
                }
                
                // Renew cookie if necessary.
                $timeout_check = $data[$this->_config['timeout_col']] < date('Y-m-d H:i:s');
                
                if (! $timeout_check) {
                    $handle = $data[$this->_config['handle_col']];
                    $this->_setToken($handle);
                }
                
                // successful login, treat result as user info
                $this->reset('VALID', $data);
                return true;
            }
            
            // invalid identifier!
        }
        
        return false;
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
                $this->_setToken($handle);
            }
        }
        
        return $info;
    }

    /**
     *
     * Creates and saves a new token in the database and sets/renews a cookie.
     *
     * @param string $handle User handle.
     *
     */
    protected function _setToken($handle)
    {
        // generate identifier
        $identifier = hash(
            $this->_config['hash_algo'],
            $this->_config['salt']
            . hash($this->_config['hash_algo'], $handle)
        );

        // generate secret token
        $token = md5(uniqid(rand(), true));

        // set timeout timestamp (in seconds)
        $timeout = time() + $this->_config['timeout'];

        // update table info
        // get the dependency object of class Solar_Sql
        $sql = Solar::dependency('Solar_Sql', $this->_config['sql']);

        $data = array(
            $this->_config['identifier_col'] => $identifier,
            $this->_config['token_col']      => $token,
            $this->_config['timeout_col']    => $timeout,
        );
        $where = array("{$this->_config['handle_col']} = ?" => $handle);
        $sql->update($this->_config['table'], $data, $where);

        // set cookie
        setcookie(
            $this->_config['cookie_name'],
            "$identifier:$token",
            $timeout,
            $this->_config['cookie_path'],
            $this->_config['cookie_domain'],
            $this->_config['cookie_secure'],
            $this->_config['cookie_httponly']
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
        $status = parent::_processLogout();
        
        // Some time in the past (one month ago).
        $lifetime = time() - 2592000;
        
        // set cookie
        setcookie(
            $this->_config['cookie_name'],
            'DELETED',
            $lifetime,
            $this->_config['cookie_path'],
            $this->_config['cookie_domain'],
            $this->_config['cookie_secure'],
            $this->_config['cookie_httponly']
        );
        
        // return status from parent
        return $status;
    }
}