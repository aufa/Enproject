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

namespace Aufa\Enproject\Collector;

/**
 * Server Collector save $_SERVER php system values
 */
class Server extends Collector
{
    /* --------------------------------------------------------------------------------*
     |                                Class Properties                                 |
     |---------------------------------------------------------------------------------|
     */

    /**
     * The prefix of HTTP headers normally
     * stored in the Server data
     *
     * @type string
     */
    protected static $x_http_header_prefix = 'HTTP_';

    /**
     * The list of HTTP headers that for some
     * reason aren't prefixed in PHP...
     *
     * @type array
     */
    protected static $x_http_nonprefixed_headers = array(
        'CONTENT_LENGTH',
        'CONTENT_TYPE',
        'CONTENT_MD5',
    );

    /* --------------------------------------------------------------------------------*
     |                                  Class Method                                   |
     |---------------------------------------------------------------------------------|
     */

    /**
     * Quickly check if a string has a passed prefix
     *
     * @param string $string    The string to check
     * @param string $prefix    The prefix to test
     * @return boolean
     */
    public static function hasPrefix($string, $prefix)
    {
        if (strpos($string, $prefix) === 0) {
            return true;
        }

        return false;
    }

    /**
     * Get our headers from our server data collection
     *
     * PHP is weird... it puts all of the HTTP request
     * headers in the $_SERVER array. This handles that
     *
     * @return array
     */
    public function getHeaders()
    {
        // Define a headers array
        $headers = array();

        foreach ($this->x_attributes as $key => $value) {
            // Does our server attribute have our header prefix?
            if (self::hasPrefix($key, self::$x_http_header_prefix)) {
                // Add our server attribute to our header array
                $headers[
                    substr($key, strlen(self::$x_http_header_prefix))
                ] = $value;
            } elseif (in_array($key, self::$x_http_nonprefixed_headers)) {
                // Add our server attribute to our header array
                $headers[$key] = $value;
            }
        }

        return $headers;
    }
}
