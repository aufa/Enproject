<?php
/**
 * Enproject Simple Framework Library
 *     Create Application using Enproject Simple Framework easily.
 * @copyright   Copyright (c) 2015 awan
 * @link        https://github.com/aufa
 * @version     0.1.0
 * @author      awan <nawa@yahoo.com>
 * @package     aufa\enproject
 * @license     GPLv3+ <https://www.gnu.org/licenses/gpl-3.0.txt>
 */

namespace Aufa\Enproject;

use Aufa\Enproject\Abstracts\Singleton;
use Aufa\Enproject\Http\Response;
use Aufa\Enproject\Http\Request;
use Aufa\Enproject\Helper\Security;

/**
 * Cookie class for Extended Helper set Application Cookie abstraction
 */
class Cookie extends Singleton
{
    /**
     * Constructor
     *
     * @param array $settings
     */
    public function __construct()
    {
        parent::__construct();
        /**
         * Session
         *
         * We must start a native PHP session to initialize the $_SESSION superglobal.
         * However, we won't be using the native session store for persistence, so we
         * disable the session cookie and cache limiter. We also set the session
         * handler to this class instance to avoid PHP's native session file locking.
         */
        ini_set('session.use_cookies', 0);
        session_cache_limiter(false);
        session_set_save_handler(
            array($this, 'open'),
            array($this, 'close'),
            array($this, 'read'),
            array($this, 'write'),
            array($this, 'destroy'),
            array($this, 'gc')
        );
    }

    /**
     * Get value of HTTP cookie from the current HTTP request
     *
     * Return the value of a cookie from the current HTTP request,
     * or return NULL if cookie does not exist. Cookies created during
     * the current request will not be available until the next request.
     *
     * @param  string      $name
     * @param  bool        $deleteIfInvalid doing delete if invalid
     * @param  bool        $encrypted       use force encryped to set true if encrypted
     *                                      without following config
     *                                      set to false if use no encryption
     * @return string|null
     */
    public static function get($name, $deleteIfInvalid = false, $encrypted = null)
    {
        // Get cookie value
        $cookies = Request::cookies();
        $value   = $cookies->get($name);
        $config  = Config::singleton();
        $prefix  = $config->get('cookie_encrypt_prefix', 'enc|');
        (is_string($prefix) && trim($prefix)) || $prefix = 'enc|';
        // Decode if encrypted
        if (($config->get('cookie_encrypt', true) && $encrypted !== false || $encrypted)
            // check prefix enc| for encryption
            && strpos($value, $prefix) === 0
        ) {
            $value = Security::decrypt(
                $value,
                sha1($config->security_key.$config->security_salt.$config->session_hash)
            );
            if ($value === null && $deleteIfInvalid) {
                static::deleteCookie($name);
            }
        }

        return $value;
    }

    /**
     * Set HTTP cookie to be sent with the HTTP response
     *
     * @param string     $name      The cookie name
     * @param string     $value     The cookie value
     * @param int|string $expires   The duration of the cookie;
     *                                  If integer, should be UNIX timestamp;
     *                                  If string, converted to UNIX timestamp with `strtotime`;
     * @param string     $path      The path on the server in which the cookie will be available on
     * @param string     $domain    The domain that the cookie is available to
     * @param bool       $secure    Indicates that the cookie should only be transmitted over a secure
     *                              HTTPS connection to/from the client
     * @param bool       $httponly  When TRUE the cookie will be made accessible only through the HTTP protocol
     * @param bool       $encrypted When TRUE the cookie will be made as encrypted
     */
    public static function set(
        $name,
        $value,
        $expires = null,
        $path = null,
        $domain = null,
        $secure = null,
        $httponly = null,
        $encrypted = null
    ) {
        $settings = array(
            'value'    => $value,
            'expires'  => is_null($expires) ? Config::get('cookie_lifetime') : $expires,
            'path'     => is_null($path) ? Config::get('cookie_path', '/') : $path,
            'domain'   => is_null($domain) ? Config::get('cookie_domain', null) : $domain,
            'secure'   => is_null($secure) ? Config::get('cookie_secure', false) : $secure,
            'httponly' => is_null($httponly) ? Config::get('cookie_httponly', false) : $httponly,
            'encrypted' => $encrypted
        );

        $cookies = Response::cookies();
        $cookies->set($name, $settings);
    }

    /**
     * Delete HTTP cookie (encrypted or unencrypted)
     *
     * Remove a Cookie from the client. This method will overwrite an existing Cookie
     * with a new, empty, auto-expiring Cookie. This method's arguments must match
     * the original Cookie's respective arguments for the original Cookie to be
     * removed. If any of this method's arguments are omitted or set to NULL, the
     * default Cookie setting values (set during Slim::init) will be used instead.
     *
     * @param string    $name       The cookie name
     * @param string    $path       The path on the server in which the cookie will be available on
     * @param string    $domain     The domain that the cookie is available to
     * @param bool      $secure     Indicates that the cookie should only be transmitted over a secure
     *                              HTTPS connection from the client
     * @param  bool     $httponly   When TRUE the cookie will be made accessible only through the HTTP protocol
     */
    public static function remove($name, $path = null, $domain = null, $secure = null, $httponly = null)
    {
        return static::set($name, '', (time() - 86400), $path, $domain, $secure, $httponly);
    }

    /**
     * Delete HTTP cookie (encrypted or unencrypted) (same with remove)
     *
     * Remove a Cookie from the client. This method will overwrite an existing Cookie
     * with a new, empty, auto-expiring Cookie. This method's arguments must match
     * the original Cookie's respective arguments for the original Cookie to be
     * removed. If any of this method's arguments are omitted or set to NULL, the
     * default Cookie setting values (set during Slim::init) will be used instead.
     *
     * @param string    $name       The cookie name
     * @param string    $path       The path on the server in which the cookie will be available on
     * @param string    $domain     The domain that the cookie is available to
     * @param bool      $secure     Indicates that the cookie should only be transmitted over a secure
     *                              HTTPS connection from the client
     * @param  bool     $httponly   When TRUE the cookie will be made accessible only through the HTTP protocol
     */
    public static function delete($name, $path = null, $domain = null, $secure = null, $httponly = null)
    {
        return static::set($name, '', (time() - 86400), $path, $domain, $secure, $httponly);
    }

    /* --------------------------------------------------------------------------------*
     |                          Session handler Callback                               |
     |---------------------------------------------------------------------------------|
     */

    /**
     * @codeCoverageIgnore
     */
    public function open($savePath, $sessionName)
    {
        return true;
    }

    /**
     * @codeCoverageIgnore
     */
    public function close()
    {
        return true;
    }

    /**
     * @codeCoverageIgnore
     */
    public function read($id)
    {
        return '';
    }

    /**
     * @codeCoverageIgnore
     */
    public function write($id, $data)
    {
        return true;
    }

    /**
     * @codeCoverageIgnore
     */
    public function destroy($id)
    {
        return true;
    }

    /**
     * @codeCoverageIgnore
     */
    public function gc($maxlifetime)
    {
        return true;
    }
}
