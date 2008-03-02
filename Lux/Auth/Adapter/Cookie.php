<?php
/**
 *
 * Persistent authentication using cookies and a SQL database table.
 *
 * Follows Chris Shiflett's recommendations in "Essential PHP Security" -
 * chapter 7.4. Persistent Logins. Added some more security measures.
 *
 * @category Lux
 *
 * @package Lux_Auth
 *
 * @author Rodrigo Moraes <rodrigo.moraes@gmail.com>
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 *
 * @version $Id$
 *
 */
class Lux_Auth_Adapter_Cookie extends Solar_Auth_Adapter
{
    /**
     *
     * User-provided configuration values. Keys are...
     *
     * `model`
     * : (string) Name of the model holding authentication data. See Tipos_Model_User.
     *   The model needs to implement the following methods:
     *
     *     fetchOneByCookieHandleAndCookieToken(string $handle, string $token)
     *     fetchOneByHandleAndPasswd(string $handle, string $passwd)
     *
     *   And the model record needs this method:
     *
     *     updateToken()
     *
     * `source_persist`
     * : (string) Persist key in the credential array source, default 'persist'.
     *
     * `cookie_handle`
     * : (int) Name of the cookie for the hashed handle.
     *
     * `cookie_token`
     * : (int) Name of the cookie for the hashed token.
     *
     * `cookie_persist`
     * : (int) Name of the cookie for the persist flag.
     *
     * `token_expire`
     * : (int) Token lifetime in seconds. After this time, it is reset and
     *   saved again in the database. Default is 86400 (one day).
     *
     * `salt`
     * : (string) A salt prefix to make cracking passwords harder.
     *
     * `salt_handle`
     * : (string) A salt prefix to hide user handles in cookies.
     *
     * `hash_algo`
     * : (string) Name of the hashing algorithm (i.e. 'md5', 'sha256',
     *   'haval160,4' etc.). Default is 'md5'.
     *
     * `cookie_expire`
     * : (int) Expiration time for setcookie(), in seconds. Default is 2592000
     *   (one week).
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
    protected $_Lux_Auth_Adapter_Cookie = array(
        'model'           => 'Tipos_Model_User',
        'source_persist'  => 'persist',
        'cookie_handle'   => 'handle',
        'cookie_token'    => 'token',
        'cookie_persist'  => 'persist',
        'token_expire'    => 86400,
        'salt'            => null,
        'salt_handle'     => null,
        'hash_algo'       => 'md5',
        // setcookie() params.
        'cookie_expire'   => 2592000,
        'cookie_path'     => '/',
        'cookie_domain'   => '',
        'cookie_secure'   => false,
        'cookie_httponly' => true,
    );

    /**
     *
     * Authenticated user object.
     *
     * @var Tipos_Model_User.
     *
     */
    public $user;

    /**
     *
     * True if a cookie authenticates, false otherwise.
     *
     * @var bool
     *
     */
    protected $_is_valid_cookie = false;

    /**
     *
     * True if the user choose a persistent authentication, false otherwise.
     *
     * @var bool
     *
     */
    protected $_persist;

    /**
     *
     * Starts a session with authentication.
     *
     * @return void
     *
     */
    public function start()
    {
        if ($this->allow && !$this->isLoginRequest()) {
            // On each request that is not a login request, try to authenticate
            // using cookies.
            $this->_is_valid_cookie = $this->_processCookieLogin();
        }

        // Start normal authentication.
        parent::start();
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
        // Get handle and token cookies.
        $handle = $this->_request->cookie($this->_config['cookie_handle']);
        $token = $this->_request->cookie($this->_config['cookie_token']);

        if ($handle && $token) {
            // Cookies are set. Try to authenticate.
            $model = Solar::factory($this->_config['model']);
            // Magic method.
            $this->user = $model->fetchOneByCookieHandleAndCookieToken($handle,
                $token);

            if ($this->user) {
                // User is authenticated.
                // Reset authentication token, if expired.
                $this->_setToken();

                // Turn persistent auth on, if a cookie is set for this.
                $this->_persist = (int) $this->_request->cookie(
                    $this->_config['cookie_persist'], 0);

                // Renew cookies only for persistent authentication.
                if ($this->_persist) {
                    $this->_setCookies();
                }

                // Set some info for the adapter.
                $info = array(
                    'handle' => $this->user->handle,
                    'uid'    => $this->user->id,
                );
                $this->reset('VALID', $info);

                // Done!
                return true;
            }
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
        // Salt and hash the password.
        $passwd = hash($this->_config['hash_algo'],
            $this->_config['salt'] . $this->_passwd);

        // Fetch the user (magic method).
        $model = Solar::factory($this->_config['model']);
        $this->user = $model->fetchOneByHandleAndPasswd(
            $this->_handle, $passwd);

        if ($this->user) {
            // User is authenticated.
            // Generate a new authentication.
            $this->_setToken();

            // Turn persistent auth on, if requested.
            $method = strtolower($this->_config['source']);
            $this->_persist = (int) $this->_request->$method(
                $this->_config['source_persist'], 0);

            $this->_setCookies();

            // Set some info for the adapter.
            $info = array(
                'handle' => $this->user->handle,
                'uid'    => $this->user->id,
            );

            // Done!
            return $info;
        }

        return false;
    }

    /**
     *
     * Creates and saves a new token if it has expired.
     *
     * @return void
     *
     */
    protected function _setToken()
    {
        // Reset token in cookie and db, but only if it has expired.
        $updated = strtotime($this->user->updated);
        $timestamp = (int) ($updated + $this->_config['token_expire']);

        if ($timestamp < time()) {
            // Salt and hash the user handle.
            $this->user->cookie_handle = hash($this->_config['hash_algo'],
                $this->_config['salt_handle'] . $this->user->handle);

            // Generate a new unique token.
            $this->user->cookie_token = hash($this->_config['hash_algo'],
                uniqid(rand(), true));

            // Save new token in database.
            $this->user->updateToken();
        }
    }

    /**
     *
     * Set or renew cookies.
     *
     */
    protected function _setCookies()
    {
        if ($this->_persist) {
            $lifetime = time() + $this->_config['cookie_expire'];
        } else {
            // Cookies will expire at the end of the session.
            $lifetime = 0;
        }

        $cookies = array(
            $this->_config['cookie_handle']  => $this->user->cookie_handle,
            $this->_config['cookie_token']   => $this->user->cookie_token,
            $this->_config['cookie_persist'] => $this->_persist,
        );

        foreach ($cookies as $key => $value) {
            $this->_setCookie($key, $value, $lifetime);
        }
    }

    /**
     *
     * Sets a cookie following config definitions.
     *
     * @param string $key Cookie key.
     *
     * @param string $value Cookie value.
     *
     * @param int $lifetime Expiration time.
     *
     */
    protected function _setCookie($key, $value, $lifetime)
    {
        setcookie($key, $value,  $lifetime,
            $this->_config['cookie_path'],
            $this->_config['cookie_domain'],
            $this->_config['cookie_secure'],
            $this->_config['cookie_httponly']
        );
    }

    /**
     *
     * Processes logout attempts.
     *
     * @return string A status code string for reset().
     *
     */
    protected function _processLogout()
    {
        parent::_processLogout();

        // Some time in the past (one month ago).
        $lifetime = time() - 2592000;

        // Set cookies with empty values and an expiration time in the past.
        $this->_setCookie($this->_config['cookie_handle'], '', $lifetime);
        $this->_setCookie($this->_config['cookie_token'], '', $lifetime);
        $this->_setCookie($this->_config['cookie_persist'], '', $lifetime);

        // Turn off validated cookies.
        $this->_is_valid_cookie = false;

        // Unset user.
        $this->user = false;

        // Return status code for 'anonymous'.
        return 'ANON';
    }

    /**
     *
     * Tells whether the current authentication is valid.
     *
     * @return bool True if the user is authenticated, false otherwise.
     *
     */
    public function isValid()
    {
        if ($this->allow && !$this->isLoginRequest()) {
            return $this->_is_valid_cookie;
        }

        return ($this->status == 'VALID');
    }
}