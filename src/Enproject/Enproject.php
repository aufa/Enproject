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
use Aufa\Enproject\Request\Server;

/**
 * Main application Project
 */
class Enproject extends Singleton
{
    /* --------------------------------------------------------------------------------*
     |                              Class Properties                                   |
     |---------------------------------------------------------------------------------|
     */

    /**
     * Record Application Object cached
     * @var array
     */
    protected static $x_record_app = array();

    /**
     * Record Latest App call
     * @var string
     */
    protected static $x_last_app;

    /**
     * List of Protected Application
     * @var array
     */
    protected static $x_protected_app = array();

    /**
     * Application name prefix global
     * @access private
     * @var string
     */
    protected static $x_app_prefix = '_';

    /**
     * Application PHP 5 constructor
     */
    public function __construct()
    {
        parent::__construct();

        /**
         * call Instance Error Handler
         */
        ErrorHandler::singleton();

        /**
         * Set Benchmark
         */
        Benchmark::set('app', 'start');

        /**
         * start buffer to make content overide
         */
        ob_start();

        /**
         * Check empty  to prevent Multiple call
         */
        if (empty(static::$x_record_app)) {
            /**
             * Prereserved Application
             * [APP, APPLICATION, ENPROJECT, CORE]
             */
            // protect it
            static::$x_protected_app = array(
                static::$x_app_prefix.'APP',
                static::$x_app_prefix.'APPLICATION',
                static::$x_app_prefix.'ENPROJECT',
                static::$x_app_prefix.'CORE',
            );
            // set it
            foreach (static::$x_protected_app as $value) {
                static::$x_record_app[$value] = $this;
            }
        }
    }

    /**
     * Get application instantly
     *
     * @param application $appname the application name
     * @param string $default default return if not exists
     */
    public function xGet($appname, $default = null)
    {
        if (is_string($appname) && trim($appname)) {
            // cached
            static $cached = array();
            $key = strtoupper(trim($appname));
            if (! isset($cached[$key])) {
                // replace backslash , underscore or slash with backslash
                $className   = trim(preg_replace('/(\\\|\/|\_)+/', '\\', $appname), '\\');
                if (stripos($className, __NAMESPACE__) !== false) {
                    $className   = trim(str_ireplace(__NAMESPACE__, '', trim($className)), '\\');
                }
                $cached[$key] = "\\".__NAMESPACE__."\\{$className}";
            }

            $className = $cached[$key];
            if (class_exists($className)
                // check if subclass of Singleton
                && is_subclass_of($className, "\\".__NAMESPACE__."\\Abstracts\\Singleton")
            ) {
                return $className::singleton();
            }

            return (
                isset(static::$x_record_app[static::$x_app_prefix.$key])
                ? static::$x_record_app[static::$x_app_prefix.$key]
                : $default
            );
        }

        return $default;
    }

    /* --------------------------------------------------------------------------------*
     |                       Instance Application Class Builder                        |
     |---------------------------------------------------------------------------------|
     */

     /* -----------------------------*
      *       STANDARD METHOD        |
      * -----------------------------|
      */

    /**
     * Check if application is exists [non static method]
     *
     * @param  string  $appname the application name
     * @return boolean          true if protected
     */
    public function xSet($appname, $object)
    {
        if (is_string($appname) && trim($appname) && is_object($object) && ! static::isProtected($appname)) {
            $appname = static::$x_app_prefix.strtoupper(trim($appname));
            static::$x_record_app[$appname] = $object;
            static::$x_last_app = $appname;
            return $this;
        }

        if (is_string($appname)) {
            if (!trim($appname)) {
                trigger_error(
                    "Invalid application name! application name could not be empty",
                    E_USER_NOTICE
                );
            } elseif (!is_object($object)) {
                trigger_error(
                    sprintf(
                        "Invalid application value! application value must be an object %s given",
                        gettype($object)
                    ),
                    E_USER_NOTICE
                );
            } else {
                trigger_error(
                    sprintf(
                        "Application name with %s has been protected! application set has no effect!",
                        trim($appname)
                    ),
                    E_USER_NOTICE
                );
            }
        } else {
            trigger_error(
                sprintf(
                    "Invalid application name! application name must be as string %s given",
                    gettype($appname)
                ),
                E_USER_NOTICE
            );
        }

        return $this;
    }

    /**
     * Protect application being override [non static method]
     *
     * @param  string|null $appname set into null or does not use value then last applicaiton will be use
     * @return object
     */
    public function xProtect($appname = null)
    {
        $using_last = ($appname === null);
        if ($using_last || ! is_array($appname) && static::isProtected($appname) === false) {
            if ($using_last) {
                $appname = static::$x_last_app;
                // check if has last
                if ($appname && ! in_array($appname, static::$x_protected_app)) {
                    static::$x_last_app = null;
                    static::$x_protected_app[] = $appname;
                }
            } elseif (is_string($appname)) {
                $appname = static::$x_app_prefix.strtoupper(trim($appname));
                static::$x_protected_app[]  = $appname;
            } elseif (is_array($appname)) {
                $appname = array_filter($appname);
                foreach ($appname as $value) {
                    if (!is_string($value) || !trim($value)) {
                        continue;
                    }
                    $this->xProtect($value);
                }
            }
        }

        return $this;
    }


    /**
     * Get application instantly
     *
     * @param application $appname the application name
     * @param [type] $default [description]
     */
    public static function get($appname, $default = null)
    {
        return static::singleton()->xGet($appname, $default);
    }

     /* -----------------------------*
      *        STATIC METHOD         |
      * -----------------------------|
      */

    /**
     * Check if application is exists
     *
     * @param  string  $appname the application name
     * @return boolean          true if protected
     */
    public static function set($appname, $object)
    {
        return self::singleton()->xSet($appname, $object);
    }

    /**
     * Check if application is exists
     *
     * @param  string  $appname the application name
     * @return boolean          true if protected
     */
    public static function exist($appname)
    {
        if (is_string($appname)) {
            $appname = strtoupper(trim($appname));
            return isset(static::$x_record_app[static::$x_app_prefix.$appname]);
        }
        return false;
    }

    /**
     * Check if application is protected
     *
     * @param  string  $appname the application name
     * @return boolean          true if protected
     */
    public static function isProtected($appname)
    {
        if (static::exist($appname)) {
            $appname = strtoupper(trim($appname));
            return (
                isset(static::$x_record_app[static::$x_app_prefix.$appname])
                && in_array(static::$x_app_prefix.$appname, static::$x_protected_app)
            );
        }
    }

    /**
     * Protect application being override
     *
     * @param  string|null $appname set into null or does not use value then last application will be use
     * @return object
     */
    public static function protect($appname = null)
    {
        return static::singleton()->XProtect($appname);
    }

    /**
     * Run the application
     *
     * @return  object $instance current singleton instance object
     */
    public static function run()
    {
        $instance = static::Create();
        // run controller
        Controller::run();
        return $instance;
    }

    /**
     * Initialize application
     *
     * @return  object $instance current singleton instance object
     */
    public static function create()
    {
        static $has_call = null;
        $instance = static::singleton();
        if (!$has_call) {
            $has_call = true;
            // for CLI used
            if (defined('STDIN')) {
                chdir(dirname(Server::get('SCRIPT_FILENAME', getcwd())));
            }
        }

        return $instance;
    }

    /* --------------------------------------------------------------------------------*
     |                                   Cookie                                        |
     |---------------------------------------------------------------------------------|
     */

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
    public static function setCookie($name, $value, $expires = null, $path = null, $domain = null, $secure = null, $httponly = null)
    {
        return Cookie::set($name, $value, $expires, $path, $domain, $secure, $httponly);
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
    public function getCookie($name, $deleteIfInvalid = false, $encrypted = null)
    {
        return Cookie::set($name, $deleteIfInvalid, $encrypted);
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
    public static function removeCookie($name, $path = null, $domain = null, $secure = null, $httponly = null)
    {
        return Cookie::remove($name, $path, $domain, $secure, $httponly);
    }

    /**
     * Delete HTTP cookie (encrypted or unencrypted) (same with removeCookie)
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
    public static function deleteCookie($name, $path = null, $domain = null, $secure = null, $httponly = null)
    {
        return Cookie::delete($name, $path, $domain, $secure, $httponly);
    }

    /**
     * Magic Method __get, get value of object property
     *
     * @param string $key property variable
     */
    public function __get($name)
    {
        return $this->xGet($name);
    }

    /**
     * Magic Method Calling Static
     * Instance Facade Application static method
     * PHP5 Magic Method
     * as FINAL!
     *
     * Handle Call Static method for curent class
     * This magic method handle by Internal of PHP
     *
     * @param string $method    the method name
     * @param array  $arguments the arguments
     */
    final public static function __callStatic($method, $arguments)
    {
        if (! method_exists(static::singleton(), $method)
            && class_exists("\\".__NAMESPACE__."\\{$method}")
            && is_subclass_of("\\".__NAMESPACE__."\\{$method}", "\\".__NAMESPACE__."\\Abstracts\\Singleton")
        ) {
            /**
             * This for call instantiate method same as class
             */
            if (method_exists("\\{$namespace}\\{$method}", $method)) {
                return call_user_func_array(array("\\".__NAMESPACE__."\\{$method}", $method), $arguments);
            } elseif (method_exists("\\".__NAMESPACE__."\\{$method}", 'Singleton')) {
                // call singleton
                return call_user_func_array(array("\\".__NAMESPACE__."\\{$method}", 'Singleton'), $arguments);
            }
        }
 
        // if does not has static method same with name class
        // and method name is has called Child Class
        $called_ = explode(
            '\\',
            strtolower(
                get_called_class()
            )
        );
        $called_ = end($called_);

        // check if method same with name class
        if (strtolower($method) === ($called_)) {
            return static::singleton();
        }

        $debug_backtrace = debug_backtrace();
        $callee = next($debug_backtrace);
        // Trigger appropriate error
        trigger_error(' Call to undefined method '
            . $callee['class']
            . $callee['type']
            . $callee['function']
            .'() in <b>'.$callee['file'].'</b> on line <b>'.$callee['line']."</b><br />\n", E_USER_ERROR);
    }
}
