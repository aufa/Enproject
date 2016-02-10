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
use Aufa\Enproject\Http\Request;
use Aufa\Enproject\Request\Server;
use Aufa\Enproject\Request\Get;
use Aufa\Enproject\Helper\Path;
use Aufa\Enproject\Helper\StringHelper;

/**
 * URL Class Request URI parser
 */
class Url extends Singleton
{
    /* --------------------------------------------------------------------------------*
     |                              Class Properties                                   |
     |---------------------------------------------------------------------------------|
     */

    /**
     * URL Segments
     * @var array
     */
    protected $x_segments = array();

    /**
     * URL String
     * @var string
     */
    protected $x_uri_string = '';

    /**
     * URL Sufix
     * @var string
     */
    protected $x_uri_sufix = '';

    /**
     * Permited Characters
     * @var string
     */
    protected $x_permitted_uri_chars_regex = 'a-z 0-9~%.:_\-';

    /**
     * Is URI Dissallowed
     * @var boolean
     */
    private $x_uri_blocked = false;

    /* --------------------------------------------------------------------------------*
     |                                Class Method                                     |
     |---------------------------------------------------------------------------------|
     */

    /**
     * PHP5 Constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->x_uri_sufix = Config::get('uri_sufix', '');
        /**
         * Set BenchMark Start
         */
        Benchmark::set('url', 'start');
        /**
         * initiate
         */
        $this->reInitiate();
        /**
         * Set BenchMark End
         */
        Benchmark::set('url', 'end');
    }

    /**
     * Reinitiate set URI
     *
     * @return void
     */
    public function reInitiate()
    {
        if (Server::isCLI()) {
            $this->setUri($this->parseArgv());
        } else {
            $this->setUri($this->parseRequestUri());
        }
    }

    /**
     * Parse CLI arguments
     * Take each command line argument and assume it is a URI segment.
     *
     * @return  string
     */
    protected function parseArgv()
    {
        $args = array_slice(Server::get('argv', array()), 1);
        return $args ? implode('/', $args) : '';
    }

    /**
     * Parse Request URL
     *
     * @return string Request URI parsed
     */
    protected function parseRequestUri()
    {
        // static cached
        static $return = null;

        if ($return !== null) {
            return $return;
        }
        if (! Server::get('REQUEST_URI') && ! Server::get('SCRIPT_NAME')) {
            $return = '';
            return $return;
        }
        // add Request URI
        // $requri = Path::cleanSlashed(Request::getHost().'/'.Server::get('REQUEST_URI'));
        $requri = Server::get('REQUEST_URI');
        $requri = substr($requri, 0, 1) == '/' ? $requri : "/{$requri}";
        $requri = rtrim(Request::getHost(), '/').$requri;
        $uri   = parse_url(
            'http://' // add some tricky trick will be use as port
            . $requri // request URI
        );
        $query = isset($uri['query']) ? $uri['query'] : '';
        $uri = isset($uri['path']) ? $uri['path'] : '';
        $script_name = Server::get('SCRIPT_NAME');
        if (isset($script_name[0])) {
            /**
             * Set New URL Path
             */
            if (strpos($uri, $script_name) === 0) {
                $uri = substr($uri, strlen($script_name));
            } elseif (strpos($uri, dirname($script_name)) === 0) {
                $uri = substr($uri, strlen(dirname($script_name)));
            }
        }
        // This section ensures that even on servers that require the URI to be in the query string (Nginx) a correct
        // URI is found, and also fixes the QUERY_STRING server var and $_GET array.
        if (trim($uri, '/') === '' && strncmp($query, '/', 1) === 0) {
            $query = explode('?', $query, 2);
            $uri = $query[0];
            $_SERVER['QUERY_STRING'] = isset($query[1]) ? $query[1] : '';
        } else {
            $_SERVER['QUERY_STRING'] = $query;
        }

        // replace server attributes
        Server::replace($_SERVER);
        // parse the string
        parse_str($_SERVER['QUERY_STRING'], $_GET);
        // replace get
        Get::replace($_GET);
        if ($uri === '/' || $uri === '') {
            $return = '/';
        } else {
            $return = Path::removeRelativeDirectory($uri);
        }
        return $return;
    }

    /**
     * Filters x_segments for malicious characters.
     *
     * @param   string  $str    String uri
     * @return  object  $this   Current class
     */
    public function filterUri(&$str)
    {
        if (! empty($str) && ! empty($this->x_permitted_uri_chars_regex)
            && ! preg_match('/^['.$this->x_permitted_uri_chars_regex.']+$/iu', $str)
        ) {
            $this->x_uri_blocked = true;
        }

        return $this;
    }

    /**
     * Set URI String
     *
     * @param   string  $str    String Uri
     * @return  object  $this   Current class
     */
    protected function setUri($str)
    {
        $this->x_uri_blocked = false;
        // Filter out control characters and trim slashes
        $this->x_uri_string = trim(StringHelper::removeInvisibleCharacters($str, false), '/');

        if ($this->x_uri_string !== '') {
            // Remove the URL suffix, if present
            if (($suffix = $this->x_uri_sufix) !== '') {
                $slen = strlen($suffix);
                if (substr($this->x_uri_string, -$slen) === $suffix) {
                    $this->x_uri_string = substr($this->x_uri_string, 0, -$slen);
                }
            }
            // add first key
            $this->x_segments[0] = null;
            // Populate the x_segments array
            foreach (explode('/', trim($this->x_uri_string, '/')) as $val) {
                $val = trim($val);
                // Filter x_segments for security
                $this->filterUri($val);
                if ($val !== '') {
                    $this->x_segments[] = $val;
                }
            }

            unset($this->x_segments[0]);
        }

        return $this;
    }

    /**
     * Chek if URI Blocked
     *
     * @return boolean true if blocked
     */
    public static function isBlocked()
    {
        return static::singleton()->x_uri_blocked;
    }

    /**
     * Returning URL String
     *
     * @return string
     */
    public static function uriString()
    {
        return static::singleton()->x_uri_string;
    }

    /**
     * Fetch URI Segment
     *
     * @see     Url::$x_segments
     * @param   int     $n          Index
     * @param   mixed   $no_result  What to return if the segment index is not found
     * @return  mixed
     */
    public static function segment($n, $no_result = null)
    {
        return isset(static::singleton()->x_segments[$n]) ? static::singleton()->x_segments[$n] : $no_result;
    }

    /**
     * Get all segments
     *
     * @return array
     */
    public static function allSegment()
    {
        return static::singleton()->x_segments;
    }
}
