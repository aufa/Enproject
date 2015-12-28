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

namespace Aufa\Enproject\Helper;

use Aufa\Enproject\Abstracts\Singleton;
use Aufa\Enproject\Request\Server;

/**
 * Url Helper class file
 */
class Url extends Singleton
{
    /**
     * Clean The uri double slashed to single slashed
     * eg : http://domain.com//do//this////uri///// to http://domain.com/do/this to prevent much 404 error
     * ;/domain.com | :/domain.com | ://domain.com | ;//domain.com to  //domain.com
     * and replace backslashed to slashed
     *
     * @param  string  $uri            the url to clean
     * @param  boolan  $replace_space  true if you want use replace sache with (+) - plus characters
     * @param  boolean $use_http       true if use http as prefix is possible when url use start as (//)
     * @return string  $result         as clean uri
     */
    public static function cleanSlashUri($uri, $replace_space = false, $use_http = false)
    {
        // if non string stop here return original
        if (! is_string($uri) || ! $uri) {
            return $uri;
        }

        static $result,
                $tmpuri = null;

        // if uri is same and has result , then will be return as before
        if ($result !== null && $tmpuri === $uri) {
            return $result;
        }

        // determine $tmpuri as $uri variables
        $tmpuri = $uri;
        $query  = '';
        if (strpos($uri, '?')) {
            $explode = explode('?', $uri);
            $uri = $explode[0];
            if (count($explode) > 1) {
                array_shift($explode);
            }
            $query = '?'.implode('?', $explode);
        }

        // clean request uri backslashed replaces, remember on windows request uri backslash ( \ ) , is not functions!!
        $slashed = preg_replace('/(\\\)+/', '/', trim($uri));
        // clean the protocol;// | protocol//
        // or  ;// | :// ( this will be only start with ; or : or double slash not single slash (/) or start with etc. )
        // hate the loop ! so save it as array variable , this value same as allowed_protocols()
        $regex     = array(
                        /**
                         * allowed only 2 slashes
                         */
                        '/\/{3,}/',
                        /**
                         * If has ; | : on start of url string will be replace with
                         * eg ://www.domain.com will be //www.domain.com
                         * must be has double slashes if not started with ; | ;
                         * and will be not affected if has start with single ( / ) slash only
                         * or fix non uri checking , if still have double slashes replace it and keep :/ as two slashes
                         */
                        '/^(:|;)*\/\/|^(:|;)\/+/',

                    );
        $protocol  = array(
                        '/',     # multiple slashes replace
                        // '$1://', # protocol replace that unsafe delimiter ( ; )
                        '//',     # replace protocol if not used like :// or ;// wit // only
                        );
        $result  = preg_replace($regex, $protocol, $slashed);

        // closure object as callback
        $tolowerfix = function ($n) {
            $n[0] = str_replace(';', ':', strtolower($n[0]));
            $n[0] = preg_replace('/^(.*)\/\//', '$1://', $n[0]);

            return preg_replace('/\:{1,}/', ':', $n[0]);
        };

        /**
         * replace unsafe protocols if use ; or multiple : or ;
         * eg : http:::::::/www.domain.com | http;//www.domain.com will be http://www.domain.com
         * http;/domain.com | http:/domain.com will be http://domain.com
         * replace only safe protocol , and make it lower
         */
        $regex =  '/^(http|https|ftp|ftps|mailto|news|irc|gopher|nntp|feed|telnet|mms|rtsp|svn|tel|fax|xmpp|rtmp)((;|:)*\/?\/?)/i';

        $result  = preg_replace_callback($regex, array($tolowerfix, '__invoke'), $result);
        // end
        // clean double slash on url after domain
        $remslash = function ($c) {
            return  preg_replace('/((.*)\:)\/(\/)?/', '$1/$3', $c[1]) . preg_replace('/(\/\/)+/', '/', $c[4]);
        };

        $regex = '/^(((.*):)?\/\/?)?(.*)/';
        $result  = preg_replace_callback($regex, array($remslash, '__invoke'), $result);
        // end
        $result  = preg_replace('/^(\/\/)?(mailto|xmpp)(\:)(\/\/)?(.*)?/', '$2$3$5', $result);

        if ($result[strlen($result)-1 ] === '/') {
            $result = rtrim($result, '/');
        }

        if ($replace_space) {
            $result = str_replace(' ', '+', $result);
        }

        if ($use_http === true && preg_match('/^\/\//', $result)) {
            $result = Server::httpProtocol().':'.$result;
        }

        return $result = $result.$query;
    }

    /**
     * Alias $this::cleanSlashUri()
     */
    public static function cleanSlashUrl($uri, $replace_space = false, $use_http = false)
    {
        return self::cleanSlashUri($uri, $replace_space, $use_http);
    }

    /**
     * Set cookie domain with .domain.ext for multi sub domain
     *
     * @param  sting  $domain
     * @return string $return domain ( .domain.com )
     */
    public static function splitCrossDomain($domain)
    {
        // domain must be string
        if (! is_string($domain)) {
            return $domain;
        }

        // make it domain lower
        $domain = strtolower($domain);
        $domain = preg_replace('/((http|ftp)s?|sftp|xmp):\/\//i', '', $domain);
        $domain = preg_replace('/\/.*$/', '', $domain);
        $is_ip = filter_var($domain, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4);
        if (!$is_ip) {
            $is_ip = filter_var($domain, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6);
        }
        if (!$is_ip) {
            $parse  = parse_url('http://'.$domain.'/');
            $domain = isset($parse['host']) ? $parse['host'] : null;
            if ($domain === null) {
                return null;
            }
        }
        if (!preg_match('/^((\[[0-9a-f:]+\])|(\d{1,3}(\.\d{1,3}){3})|[a-z0-9\-\.]+)(:\d+)?$/i', $domain)
            || $is_ip
            || $domain == '127.0.0.1'
            || $domain == 'localhost'
            ) {
            return $domain;
        }

        $domain = preg_replace('/[~!@#$%^&*()+`\{\}\]\[\/\\\'\;\<\>\,\"\?\=\|]/', '', $domain);
        if (strpos($domain, '.') !== false) {
            if (preg_match('/(.*\.)+(.*\.)+(.*)/', $domain)) {
                $return     = '.'.preg_replace('/(.*\.)+(.*\.)+(.*)/', '$2$3', $domain);
            } else {
                $return = '.'.$domain;
            }
        } else {
            $return = $domain;
        }
        
        return $return;
    }
}
