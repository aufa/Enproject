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

namespace Aufa\Enproject\Http;

use Aufa\Enproject\Abstracts\Singleton;
use Aufa\Enproject\Config;
use Aufa\Enproject\ErrorHandler;
use Aufa\Enproject\Helper\Security;
use Aufa\Enproject\Cryptography\Sha1;
use IteratorAggregate;

/**
 * Response handler
 */
class Response extends Singleton implements IteratorAggregate
{
    /**
     * body content
     * @var string
     */
    protected $x_body = '';

    /**
     * Body length
     * @var integer
     */
    protected $x_length = 0;

    /**
     * HTTP Cookies
     * @var object \Aufa\Enproject\Http\Cookie
     */
    protected $cookies;

    /**
     * PHP5 Constructor
     *
     * @param string $body    The HTTP response body
     * @param int    $status  The HTTP response status
     * @param array  $headers The HTTP response headers
     */
    public function __construct($body = '', $status = 200, $headers = array())
    {
        parent::__construct();
        static::setStatus($status);
        Headers::has('Content-Type') || Headers::replace(array('Content-Type' => 'text/html'));
        Headers::replace($headers);
        $this->cookies = new Cookie();
        static::write($body);
    }

    /**
     * Get Cookies Value
     *
     * @param null|string $name nulll if want get object cookie record
     */
    public static function cookies($name = null)
    {
        $instance = static::singleton();
        if ($name) {
            return $instance->cookies->get($name);
        }

        return $instance->cookies;
    }

    /**
     * Get header status code
     *
     * @return integer HTTP Status code
     */
    public static function getStatus()
    {
        return Headers::getCode();
    }

    /**
     * Set Header status
     *
     * @param integer $status HTTP Status code
     */
    public static function setStatus($status)
    {
        // set status
        Headers::setStatus($status);
        return static::singleton();
    }

    /**
     * Set Header
     *
     * @param string $key   Header key name
     * @param string $value value of header key name
     */
    public static function set($key, $value)
    {
        Headers::Set($key, $value);
    }

    /**
     *
     * Get and set header
     *
     * @param  string      $name  Header name
     * @param  string|null $value Header value
     * @return string      Header value
     */
    public static function header($name, $value = null)
    {
        if (!is_null($value)) {
            Headers::set($name, $value);
        }

        return Headers::get($name);
    }

    /**
     * Get Body Content
     *
     * @return string   $body property
     */
    public static function getBody()
    {
        return static::singleton()->x_body;
    }

    /**
     * Use Headers::singleton() // directly is same
     *
     * @return object Enproject\Header
     */
    public static function getHeader()
    {
        return Headers::singleton();
    }

    /**
     * Set Body content
     *
     * @param string $content The body Content
     */
    public static function setBody($content)
    {
        return static::singleton()->write($content, true);
    }

    /**
     * Append the body Content
     *
     * @param  string $content Content string to append
     */
    public static function append($content)
    {
        return static::singleton()->write($content, false);
    }

    /**
     * Append the body Content
     *
     * @param  string $content Content string to append
     */
    public static function prepend($content)
    {
        $instance = static::singleton();
        $instance->x_body = ((string) $content).$instance->x_body;
        $instance->x_length = strlen($instance->x_body);

        return $instance->x_body;
    }

    /**
     * Append / set HTTP response body
     * @param  string   $body       Content to append to the current HTTP response body
     * @param  bool     $replace    Overwrite existing response body?
     * @return string               The updated HTTP response body
     */
    public static function write($body, $replace = false)
    {
        $instance = static::singleton();
        if ($replace) {
            $instance->x_body = $body;
        } else {
            $instance->x_body .= (string) $body;
        }
        $instance->x_length = strlen($instance->x_body);

        return $instance->x_body;
    }

    /**
     * Get body content Length
     *
     * @return string length content
     */
    public static function getLength()
    {
        return static::singleton()->x_length;
    }

    /**
     * Helpers: Empty?
     *
     * @return boolean
     */
    public static function isEmpty()
    {
        return in_array(self::getStatus(), array(201, 204, 304));
    }

    /**
     * Helpers: Informational?
     *
     * @return boolean
     */
    public static function isInformational()
    {
        return self::getStatus() >= 100 && self::getStatus() < 200;
    }

    /**
     * Helpers: OK?
     *
     * @return boolean
     */
    public static function isOk()
    {
        return self::getStatus() === 200;
    }

    /**
     * Helpers: Successful?
     *
     * @return boolean
     */
    public static function isSuccessful()
    {
        return self::getStatus() >= 200 && self::getStatus() < 300;
    }

    /**
     * Helpers: Redirect?
     *
     * @return boolean
     */
    public static function isRedirect()
    {
        return in_array(self::getStatus(), array(301, 302, 303, 307));
    }

    /**
     * Helpers: Redirection?
     *
     * @return boolean
     */
    public static function isRedirection()
    {
        return self::getStatus() >= 300 && self::getStatus() < 400;
    }

    /**
     * Helpers: Forbidden?
     *
     * @return boolean
     */
    public static function isForbidden()
    {
        return self::getStatus() === 403;
    }

    /**
     * Helpers: Not Found?
     *
     * @return boolean
     */
    public static function isNotFound()
    {
        return self::getStatus() === 404;
    }

    /**
     * Helpers: Client error?
     *
     * @return boolean
     */
    public static function isClientError()
    {
        return self::getStatus() >= 400 && self::getStatus() < 500;
    }

    /**
     * Helpers: Server Error?
     *
     * @return boolean
     */
    public static function isServerError()
    {
        return self::getStatus() >= 500 && self::getStatus() < 600;
    }

    /* --------------------------------------------------------------------------------*
     |                                 ArrayAccess                                     |
     |---------------------------------------------------------------------------------|
     */

    /**
     * Array Access: Offset Exists
     */
    public function offsetExists($offset)
    {
        return Headers::offsetExists($offset);
    }

    /**
     * Array Access: Offset Get
     */
    public function offsetGet($offset)
    {
        return Headers::offsetGet($offset);
    }

    /**
     * Array Access: Offset Set
     */
    public function offsetSet($offset, $value)
    {
        Headers::offsetSet($offset, $value);
    }

    /**
     * Array Access: Offset Unset
     */
    public function offsetUnset($offset)
    {
        Headers::offsetUnset($offset);
    }

    /**
     * Countable: Count
     */
    public function count()
    {
        return Headers::count();
    }

    /**
     * Iterator
     */
    public function getIterator()
    {
        return Headers::getIterator();
    }

    /**
     * Redirect
     *
     * This method prepares this response to return an HTTP Redirect response
     * to the HTTP client.
     *
     * @param string $url    The redirect destination
     * @param int    $status The redirect HTTP status code
     */
    public static function redirect($url, $status = 302)
    {
        static::setStatus($status);
        Headers::set('Location', $url);
    }

    /**
     * Get message for HTTP status code
     *
     * @param  int         $status
     * @return string|null
     */
    public static function getMessageForCode($status)
    {
        return "{$status} ".Headers::httpMessage($status);
    }

    /**
     * Finalize
     *
     * This prepares this response and returns an array
     * of [status, headers, body].
     *
     * @return array[int status, array headers, string body]
     */
    public static function finalize()
    {
        // Prepare response
        if (in_array(static::getStatus(), array(204, 304))) {
            Headers::remove('Content-Type');
            Headers::remove('Content-Length');
            static::setBody('');
        }

        $instance = static::singleton();
        return array($instance::getStatus(), $instance::getHeader(), $instance->x_body);
    }

    /**
     * Serialize Response cookies into raw HTTP header
     *
     * @param  \Enproject\ErSysDucation\Response\Header $header The Response header
     */
    public static function serializeCookies(Headers &$header)
    {
        $instance = static::singleton();
        $config  = Config::singleton();
        $cookies = $instance->cookies();
        $prefix  = $config->get('cookie_encrypt_prefix', 'enc|');
        (is_string($prefix) && trim($prefix)) || $prefix = 'enc|';
        $config->cookie_encrypt = $config->get('cookie_encrypt', true);
        foreach ($cookies as $name => $settings) {
            if (is_string($settings['expires'])) {
                $expires = strtotime($settings['expires']);
            } else {
                $expires = (int) $settings['expires'];
            }

            /**
             * Check if is has encrypted value
             *     if config cookie encrypt has true
             *     and
             *     (__ settings['encrypted'] = has null or not exists)
             *     or not empty $settings['encrypted']
             * @var boolean
             */
            if (! empty($settings['encrypted']) || $config->cookie_encrypt && ! isset($settings['encrypted'])) {
                // add prefix enc to make sure if cookie has encrypt
                $settings['value'] = $prefix.Security::encrypt(
                    $settings['value'],
                    Sha1::hash(
                        $config->security_key
                        .$config->security_salt
                        .$config->session_hash
                    )
                );
            }
            /**
             * Cookie only accept 4KB
             */
            if (strlen($settings['value']) > 4096) {
                ErrorHandler::set(
                    E_USER_WARNING,
                    sprintf(
                        'Cookie %s has been generate more than 4KB failed to save! if there was cookie before, it will be not replaced!',
                        $name
                    ),
                    __FILE__,
                    __LINE__
                );
            } else {
                // set header cookies
                static::setCookieHeader($header, $name, $settings);
            }
        }
    }

    /**
     * Set HTTP cookie header
     *
     * This method will construct and set the HTTP `Set-Cookie` header.
     * Uses this method instead of PHP's native `setcookie` method. This allows
     * more control of the HTTP header irrespective of the native implementation's
     * dependency on PHP versions.
     *
     * @param  array  $header
     * @param  string $name
     * @param  string $value
     */
    public static function setCookieHeader(&$header, $name, $value)
    {
        //Build cookie header
        if (is_array($value)) {
            $domain = '';
            $path = '';
            $expires = '';
            $secure = '';
            $httponly = '';
            if (isset($value['domain']) && $value['domain']) {
                $domain = '; domain=' . $value['domain'];
            }
            if (isset($value['path']) && $value['path']) {
                $path = '; path=' . $value['path'];
            }
            if (isset($value['expires'])) {
                if (is_string($value['expires'])) {
                    $timestamp = strtotime($value['expires']);
                } else {
                    $timestamp = (int) $value['expires'];
                }
                if ($timestamp !== 0) {
                    $expires = '; expires=' . gmdate('D, d-M-Y H:i:s e', $timestamp);
                }
            }
            if (isset($value['secure']) && $value['secure']) {
                $secure = '; secure';
            }
            if (isset($value['httponly']) && $value['httponly']) {
                $httponly = '; HttpOnly';
            }
            $cookie = sprintf('%s=%s%s', urlencode($name), urlencode((string) $value['value']), $domain . $path . $expires . $secure . $httponly);
        } else {
            $cookie = sprintf('%s=%s', urlencode($name), urlencode((string) $value));
        }
        //Set cookie header
        if (!isset($header['Set-Cookie']) || $header['Set-Cookie'] === '') {
            $header['Set-Cookie'] = $cookie;
        } else {
            $header['Set-Cookie'] = implode("\n", array($header['Set-Cookie'], $cookie));
        }
    }

    /**
     * Parse cookie header
     *
     * This method will parse the HTTP request's `Cookie` header
     * and extract cookies into an associative array.
     *
     * @param  string
     * @return array
     */
    public static function parseCookieHeader($header)
    {
        $cookies = array();
        $header = rtrim($header, "\r\n");
        $headerPieces = preg_split('@\s*[;,]\s*@', $header);
        foreach ($headerPieces as $c) {
            $cParts = explode('=', $c, 2);
            if (count($cParts) === 2) {
                $key = urldecode($cParts[0]);
                $value = urldecode($cParts[1]);
                if (!isset($cookies[$key])) {
                    $cookies[$key] = $value;
                }
            }
        }

        return $cookies;
    }
}
