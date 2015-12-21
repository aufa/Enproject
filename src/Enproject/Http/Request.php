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
use Aufa\Enproject\Helper\Internal;
use Aufa\Enproject\Helper\Path;
use Aufa\Enproject\Request\Files;
use Aufa\Enproject\Request\Get;
use Aufa\Enproject\Request\Post;
use Aufa\Enproject\Request\Server;

/**
 * handle Request Application Record
 */
class Request extends Singleton
{
    /* --------------------------------------------------------------------------------*
     |                                Class Constant                                   |
     |---------------------------------------------------------------------------------|
     */

    /**
     * Method HEAD
     */
    const HEAD = 'HEAD';

    /**
     * Method GET
     */
    const GET = 'GET';

    /**
     * Method POST
     */
    const POST = 'POST';

    /**
     * Method PUT
     */
    const PUT = 'PUT';

    /**
     * Method PATCH
     */
    const PATCH = 'PATCH';

    /**
     * Method DELETE
     */
    const DELETE = 'DELETE';

    /**
     * Method OPTIONS
     */
    const OPTIONS = 'OPTIONS';

    /**
     * Method CLI
     * @especially only if application use CLI mode
     */
    const CLI = 'CLI';

    /* --------------------------------------------------------------------------------*
     |                                Class Properties                                 |
     |---------------------------------------------------------------------------------|
     */

    /**
     * HTTP Cookies
     * @var object \Collector
     */
    protected $cookies;

    /**
     * @var array
     */
    protected static $x_formDataMediaTypes = array('application/x-www-form-urlencoded');

    /**
     * PHP5 Constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->cookies = new Collector\Collector(Response::parseCookieHeader(Header::get('HTTP_COOKIE')));
    }

    /**
     * Get Cookies Value
     * @param  null|string $name nulll if want get object cookie record
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
     * Get HTTP method
     * @return string
     */
    public static function getMethod()
    {
        return Internal::strToUpper(Server::Get('REQUEST_METHOD'));
    }

    /**
     * Is this a GET request?
     *
     * @return boolean
     */
    public static function isGet()
    {
        return static::getMethod() === self::GET;
    }

    /**
     * Is this a POST request?
     *
     * @return boolean
     */
    public static function isPost()
    {
        return static::getMethod() === self::POST;
    }

    /**
     * Is this a PUT request?
     *
     * @return boolean
     */
    public static function isPut()
    {
        return static::getMethod() === self::PUT;
    }

    /**
     * Is this a PATCH request?
     *
     * @return boolean
     */
    public static function isPatch()
    {
        return static::getMethod() === self::PATCH;
    }

    /**
     * Is this a DELETE request?
     *
     * @return boolean
     */
    public static function isDelete()
    {
        return static::getMethod() === self::DELETE;
    }

    /**
     * Is this a HEAD request?
     *
     * @return boolean
     */
    public static function isHead()
    {
        return static::getMethod() === self::HEAD;
    }

    /**
     * Is this a OPTIONS request?
     *
     * @return boolean
     */
    public static function isOptions()
    {
        return static::getMethod() === self::OPTIONS;
    }

    /**
     * Is this a OPTIONS request?
     *
     * @return boolean
     */
    public static function isCLI()
    {
        return Server::isCLI();
    }

    /**
     * Fetch GET and POST data
     *
     * This method returns a union of GET and POST data as a key-value array, or the value
     * of the array key if requested; if the array key does not exist, NULL is returned,
     * unless there is a default value specified.
     *
     * @param  string           $key
     * @param  mixed            $default
     * @return array|mixed|null
     */
    public static function params($key = null, $default = null)
    {
        $union = array_merge(Get::all(), Post::all());
        if ($key) {
            return isset($union[$key]) ? $union[$key] : $default;
        }

        return $union;
    }

    /**
     * Is this an AJAX request?
     *
     * @return boolean
     */
    public static function isAjax()
    {
        return (Header::Get('X_REQUESTED_WITH') && Header::Get('X_REQUESTED_WITH') === 'XMLHttpRequest');
    }

    /**
     * Is this an XHR request? (alias of Slim_Http_Request::isAjax)
     * @return boolean
     */
    public function isXhr()
    {
        return static::isAjax();
    }

    /**
     * Post Server parameter Request
     *
     * @param  string $key     Key Name
     * @param  mix    $default default returning value if not exist
     * @return mixed
     */
    public static function post($key, $default = null)
    {
        return Post::post($key, $default);
    }

    /**
     * Put [POST] Server parameter Request
     *
     * @param  string $key     Key Name
     * @param  mix    $default default returning value if not exist
     * @return mixed
     */
    public static function put($key, $default = null)
    {
        return Post::post($key, $default);
    }

    /**
     * Patch [POST] Server parameter Request
     *
     * @param  string $key     Key Name
     * @param  mix    $default default returning value if not exist
     * @return mixed
     */
    public static function patch($key, $default = null)
    {
        return Post::post($key, $default);
    }

    /**
     * Delete [POST] Server parameter Request
     *
     * @param  string $key     Key Name
     * @param  mix    $default default returning value if not exist
     * @return mixed
     */
    public static function delete($key, $default = null)
    {
        return Post::post($key, $default);
    }

    /**
     * Get Server parameter Request
     *
     * @param  string $key     Key Name
     * @param  mix    $default default returning value if not exist
     * @return mixed
     */
    public static function get($key, $default = null)
    {
        return Get::get($key, $default);
    }

    /**
     * Get Content Type
     *
     * @return string|null
     */
    public static function getContentType()
    {
        return Header::get('CONTENT_TYPE');
    }

    /**
     * Get Media Type (type/subtype within Content Type header)
     *
     * @return string|null
     */
    public static function getMediaType()
    {
        $contentType = static::getContentType();
        if ($contentType) {
            $contentTypeParts = preg_split('/\s*[;,]\s*/', $contentType);

            return Internal::strToLower($contentTypeParts[0]);
        }

        return null;
    }

    /**
     * Get Media Type Params
     *
     * @return array
     */
    public static function getMediaTypeParams()
    {
        $contentType = static::getContentType();
        $contentTypeParams = array();
        if ($contentType) {
            $contentTypeParts = preg_split('/\s*[;,]\s*/', $contentType);
            $contentTypePartsLength = count($contentTypeParts);
            for ($i = 1; $i < $contentTypePartsLength; $i++) {
                $paramParts = explode('=', $contentTypeParts[$i]);
                $contentTypeParams[strtolower($paramParts[0])] = $paramParts[1];
            }
        }

        return $contentTypeParams;
    }

    /**
     * Get Content Charset
     *
     * @return string|null
     */
    public static function getContentCharset()
    {
        $mediaTypeParams = static::getMediaTypeParams();
        if (isset($mediaTypeParams['charset'])) {
            return $mediaTypeParams['charset'];
        }

        return null;
    }

    /**
     * Get Content-Length
     *
     * @return int
     */
    public static function getContentLength()
    {
        return Header::get('CONTENT_LENGTH', 0);
    }

    /**
     * Get Client IP
     *
     * @return string
     */
    public static function getIp()
    {
        $keys = array('X_FORWARDED_FOR', 'HTTP_X_FORWARDED_FOR', 'CLIENT_IP', 'REMOTE_ADDR');
        foreach ($keys as $key) {
            if (Server::get($key)) {
                return Server::get($key);
            }
        }

        return Server::get('REMOTE_ADDR');
    }

    /**
     * Get Referrer
     *
     * @return string|null
     */
    public static function getReferrer()
    {
        return Header::get('HTTP_REFERER');
    }

    /**
     * Get Referer (for those who can't spell)
     *
     * @return string|null
     */
    public static function getReferer()
    {
        return static::getReferrer();
    }

    /**
     * Get User Agent
     *
     * @return string|null
     */
    public static function getUserAgent()
    {
        return Header::get('HTTP_USER_AGENT');
    }

    /**
     * Get Host
     *
     * @return string
     */
    public static function getHost()
    {
        if (Server::get('HTTP_HOST')) {
            if (strpos(Server::get('HTTP_HOST'), ':') !== false) {
                $hostParts = explode(':', Server::get('HTTP_HOST'));

                return $hostParts[0];
            }

            return Server::get('HTTP_HOST');
        }

        return Server::get('SERVER_NAME');
    }

    /**
     * Get Host with Port
     *
     * @return string
     */
    public static function getHostWithPort()
    {
        return sprintf('%s:%s', static::getHost(), static::getPort());
    }

    /**
     * Get Port
     *
     * @return int
     */
    public static function getPort()
    {
        return (int) Server::get('SERVER_PORT');
    }

    /**
     * Get Scheme (https or http)
     *
     * @return string
     */
    public static function getScheme()
    {
        return Server::httpProtocol();
    }

    /**
     * Get Script Name (physical path)
     *
     * @return string
     */
    public static function getScriptName()
    {
        return Server::get('SCRIPT_NAME');
    }

    /**
     * Getting Root URL as Main URI by default using protocol://domain/script.php
     *
     * @return string
     */
    public static function getRootUri()
    {
        // applying hook for root_uri
        $url = Hook::apply('x_request_root_uri', static::getScriptName());
        return $url;
    }

    /**
     * Get Path (physical path + virtual path)
     *
     * @return string
     */
    public static function getPath()
    {
        return static::getScriptName() . static::getPathInfo();
    }

    /**
     * Get Path Info (virtual path)
     *
     * @return string
     */
    public static function getPathInfo()
    {
        return Server::get('PATH_INFO');
    }

    /**
     * LEGACY: Get Resource URI (alias for Slim_Http_Request::getPathInfo)
     *
     * @return string
     */
    public static function getResourceUri()
    {
        return static::getPathInfo();
    }

    /* --------------------------------------------------------------------------------*
     |                                      URL                                        |
     |---------------------------------------------------------------------------------|
     */

    /**
     * Get URL (scheme + host [ + port if non-standard ])
     *
     * @return string
     */
    public static function getUrl()
    {
        $url = static::getScheme() . '://' . static::getHost();
        if ((static::getScheme() === 'https' && static::getPort() !== 443) || (static::getScheme() === 'http' && static::getPort() !== 80)) {
            $url .= sprintf(':%s', static::getPort());
        }

        return $url;
    }

    /**
     * Get base url with path
     *
     * @param  string $path path after url
     * @return string
     */
    public static function baseUrl($path = '')
    {
        $uri = static::getUrl();
        $path = (string) $path;
        $path = ltrim($path, '/');
        if ($path) {
            $uri .= '/'.$path;
        }
        return $uri;
    }

    /**
     * Get base url with path
     *
     * @param  string $path path after url
     * @return string
     */
    public static function rootUrl($path = '')
    {
        $uri = static::getUrl().static::getRootUri();
        $path = (string) $path;
        $path = ltrim($path, '/');
        if ($path) {
            $uri .= '/'.$path;
        }
        return $uri;
    }
}
