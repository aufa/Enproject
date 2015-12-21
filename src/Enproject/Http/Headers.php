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
use Aufa\Enproject\Helper\Filter;
use ArrayAccess;
use ArrayIterator;
use Countable;
use IteratorAggregate;

/**
 * Headers Response
 */
class Headers extends Singleton implements ArrayAccess, Countable, IteratorAggregate
{
    /**
     * Key-value array of arbitrary data
     * @var array
     */
    protected $x_data = array();

    /**
     * Special-case HTTP headers that are otherwise unidentifiable as HTTP headers.
     * Typically, HTTP headers in the $_SERVER array will be prefixed with
     * `HTTP_` or `X_`. These are not so we list them here for later reference.
     *
     * @var array
     */
    protected static $x_special = array(
        'CONTENT_TYPE',
        'CONTENT_LENGTH',
        'PHP_AUTH_USER',
        'PHP_AUTH_PW',
        'PHP_AUTH_DIGEST',
        'AUTH_TYPE'
    );

    /**
     * The HTTP status code
     *
     * @type int
     */
    protected $x_code;

    /**
     * The HTTP status message
     *
     * @type string
     */
    protected $x_message;

    /**
     * HTTP 1.1 status messages based on code
     *
     * @link http://www.w3.org/Protocols/rfc2616/rfc2616-sec10.html
     * @type array
     */
    protected static $x_http_messages = array(
        // Informational 1xx
        100 => 'Continue',
        101 => 'Switching Protocols',

        // Successful 2xx
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-Authoritative Information',
        204 => 'No Content',
        205 => 'Reset Content',
        206 => 'Partial Content',

        // Redirection 3xx
        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Found',
        303 => 'See Other',
        304 => 'Not Modified',
        305 => 'Use Proxy',
        306 => '(Unused)',
        307 => 'Temporary Redirect',

        // Client Error 4xx
        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Timeout',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Request Entity Too Large',
        414 => 'Request-URI Too Long',
        415 => 'Unsupported Media Type',
        416 => 'Requested Range Not Satisfiable',
        417 => 'Expectation Failed',

        // Server Error 5xx
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Timeout',
        505 => 'HTTP Version Not Supported',
    );

    /**
     * Constructor
     *
     * @param int $code The HTTP code
     * @param string $message (optional) HTTP message for the corresponding code
     */
    public function __construct($items = array(), $code = 200, $message = null)
    {
        parent::__construct();
        static::replace($items);
        // set code
        static::setCode($code);
        if (null === $message) {
            $message = static::httpMessage($code);
        }

        $this->x_message = $message;
    }

    /* --------------------------------------------------------------------------------*
     |                                  HTTP STATUS                                    |
     |---------------------------------------------------------------------------------|
     */

    /**
     * Set the HTTP status code and message
     *
     * @param int $code
     * @return HttpStatus
     */
    public static function setCode($code)
    {
        $instance = static::singleton();
        $instance->x_code = (int) $code;
        return static::singleton();
    }

    /**
     * Get the HTTP status code
     *
     * @return int
     */
    public static function getCode()
    {
        return static::singleton()->x_code;
    }

    /**
     * Get the HTTP status message
     *
     * @return string
     */
    public static function getMessage()
    {
        return static::singleton()->x_message;
    }

    /**
     * Set the HTTP status message
     *
     * @param string $message
     * @return HttpStatus
     */
    public static function setMessage($message)
    {
        $instance = static::singleton();
        $instance->x_message = (string) $message;
        return $instance;
    }

    /**
     * Set the HTTP status code
     *
     * @param int $code
     * @return HttpStatus
     */
    public static function setStatus($code)
    {
        static::setCode($code);
        static::setMessage(static::httpMessage($code));
        return static::singleton();
    }

    /**
     * Get HTTP 1.1 message from passed code
     *
     * Returns null if no corresponding message was
     * found for the passed in code
     *
     * @param int $int
     * @return string|null
     */
    public static function httpMessage($int)
    {
        if (isset(static::$x_http_messages[$int])) {
            return static::$x_http_messages[$int];
        } else {
            return null;
        }
    }

    /**
     * Get a string representation of HTTP status
     *
     * @return string
     */
    public static function httpFormattedString()
    {
        $instance = static::singleton();
        $string = (string) $instance->x_code;

        if (null !== $instance->x_message) {
            $string = $string . ' ' . $instance->x_message;
        }

        return $string;
    }

    /* --------------------------------------------------------------------------------*
     |                                     HEADER                                      |
     |---------------------------------------------------------------------------------|
     */

    /**
     * Extract HTTP headers from an array of data (e.g. $_SERVER)
     *
     * @param  array $data
     * @return array
     */
    public static function extract($data)
    {
        $results = array();
        foreach ($data as $key => $value) {
            $key = strtoupper($key);
            if (strpos($key, 'X_') === 0 || strpos($key, 'HTTP_') === 0 || in_array($key, static::$x_special)) {
                if ($key === 'HTTP_CONTENT_LENGTH') {
                    continue;
                }
                $results[$key] = $value;
            }
        }

        return $results;
    }

    /**
     * Transform header name into canonical form
     *
     * @param  string $key
     * @return string
     */
    protected static function normalizeKey($key)
    {
        $key = strtolower($key);
        $key = str_replace(array('-', '_'), ' ', $key);
        $key = preg_replace('#^http #', '', $key);
        $key = ucwords($key);
        $key = str_replace(' ', '-', $key);

        return $key;
    }

    /**
     * Set data key to value
     *
     * @param string $key   The data key
     * @param mixed  $value The data value
     */
    public static function set($key, $value)
    {
        static::singleton()->x_data[static::normalizeKey($key)] = $value;
    }

    /**
     * Get data value with key
     *
     * @param  string $key     The data key
     * @param  mixed  $default The value to return if data key does not exist
     * @return mixed           The data value, or the default value
     */
    public static function get($key, $default = null)
    {
        $instance = static::singleton();
        if ($instance::has($key)) {
            $isInvokable = is_object($instance->x_data[static::normalizeKey($key)]) && method_exists($instance->x_data[static::normalizeKey($key)], '__invoke');

            return $isInvokable ? $instance->x_data[static::normalizeKey($key)]($instance) : $instance->x_data[static::normalizeKey($key)];
        }

        return $default;
    }

    /**
     * Add data to set
     *
     * @param array $items Key-value array of data to append to this set
     */
    public static function replace($items)
    {
        foreach ($items as $key => $value) {
            static::set($key, $value); // Ensure keys are normalized
        }
    }

    /**
     * Fetch set data
     *
     * @return array This set's key-value data array
     */
    public static function all()
    {
        return static::singleton()->x_data;
    }

    /**
     * Fetch set data keys
     *
     * @return array This set's key-value data array keys
     */
    public static function keys()
    {
        return array_keys(self::all());
    }

    /**
     * Does this set contain a key?
     *
     * @param  string  $key The data key
     * @return boolean
     */
    public static function has($key)
    {
        return array_key_exists(static::normalizeKey($key), self::all());
    }

    /**
     * Remove value with key from this set
     *
     * @param  string $key The data key
     */
    public static function remove($key)
    {
        $instance = static::singleton();
        unset($instance->x_data[static::normalizeKey($key)]);
    }

    /* --------------------------------------------------------------------------------*
     |                                Overloading                                      |
     |---------------------------------------------------------------------------------|
     */

    public function __get($key)
    {
        return static::get($key);
    }

    public function __set($key, $value)
    {
        static::set($key, $value);
    }

    public function __isset($key)
    {
        return static::has($key);
    }

    public function __unset($key)
    {
        static::remove($key);
    }

    /**
     * Clear all values
     */
    public static function clear()
    {
        static::singleton()->x_data = array();
    }

    /**
     * Array Access
     */
    public function offsetExists($offset)
    {
        return static::has($offset);
    }

    public function offsetGet($offset)
    {
        return static::get($offset);
    }

    public function offsetSet($offset, $value)
    {
        static::set($offset, $value);
    }

    public function offsetUnset($offset)
    {
        static::remove($offset);
    }

    /* --------------------------------------------------------------------------------*
     |                                 Countable                                       |
     |---------------------------------------------------------------------------------|
     */

    public function count()
    {
        return count(static::all());
    }

    /* --------------------------------------------------------------------------------*
     |                              IteratorAgregate                                   |
     |---------------------------------------------------------------------------------|
     */

    public function getIterator()
    {
        return new ArrayIterator(static::all());
    }
}
