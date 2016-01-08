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
 * Handling Path Helper That Need for System
 */
class Path extends Singleton
{
    /**
     * Remove relative directory (../) and multi slashes (///)
     *
     * Do some final cleaning of the URI and return it, currently only used in self::_parse_request_uri()
     *
     * @param   string  $url
     * @return  string
     */
    public static function removeRelativeDirectory($uri)
    {
        if (!is_string($uri)) {
            return null;
        }
        $uris = array();
        $tok = strtok($uri, '/');
        while ($tok !== false) {
            if ((! empty($tok) or $tok === '0') && $tok !== '..') {
                $uris[] = $tok;
            }
            $tok = strtok('/');
        }

        return implode('/', $uris);
    }

    /**
     * Cleaning path for window directory separator and trim right the separator '/'
     *
     * @param  string $path path
     * @return string       cleaned path
     */
    public static function cleanPath($path)
    {
        if (is_string($path)) {
            return rtrim(static::cleanSlashed($path), '/');
        }
        if (!is_array($path) && !is_object($path)) {
            return $path;
        }
    }

    /**
     * Clan Invalid Slashed to be only one slashed on separate
     *
     * @param  mixed $path  path to be cleaned
     */
    public static function cleanSlashed($path)
    {
        if (is_array($path)) {
            foreach ($path as $key => $value) {
                $path[$key] = self::cleanSlashed($value);
            }
        }
        if (is_object($path)) {
            foreach (get_object_vars($path) as $key => $value) {
                $path->{$key} = self::cleanSlashed($value);
            }
        }
        if (is_string($path)) {
            static $path_tmp = array();
            $path_tmp[$path] = isset($path_tmp[$path])
                ? $path_tmp[$path]
                : preg_replace('/(\\\|\/)+/', '/', $path);
            return $path_tmp[$path];
        }
        return $path;
    }

    /**
     * get DOCUMENT_ROOT of the web
     *
     * @return  string path document root
     */
    public static function documentRoot()
    {
        static $root;
        if (is_null($root)) {
            $directory = !empty($_SERVER['DOCUMENT_ROOT'])
                ? realpath($_SERVER['DOCUMENT_ROOT'])
                : (
                    !empty($_SERVER['CONTEXT_DOCUMENT_ROOT'])
                    ? $_SERVER['CONTEXT_DOCUMENT_ROOT']
                    : substr(
                        rtrim(self::currentRootpath(), '/'),
                        0,
                        -(rtrim(strlen(dirname($_SERVER['SCRIPT_NAME'])), '/'))
                    )
                );
                $root = rtrim(self::cleanSlashed($directory), '/');
         }

        return $root;
    }

    /**
     * Checking current request File as Root path
     *
     * @return string base directory of root path
     */
    public static function currentRootpath()
    {
        static $root;
        if (!$root) {
            $root =  static::cleanSlashed(dirname(Server::get('SCRIPT_FILENAME')));
        }
        return $root;
    }

    /**
     * Geting path after root Path , this will be shown after
     *     Root path only
     * @param  string $path the path to be clean, make sure to get right values
     *                      checking path (path) must be check on after root path
     * @return string       if match path with root path
     */
    public static function getPathAfterRoot($path)
    {
        $root = static::currentRootpath();
        $path = rtrim(self::cleanSlashed($path), '/');
        if (strpos($path, $root) !== false) {
            $path =  substr($path, strlen($root));
            return '/'.trim($path, '/');
        }

        return null;
    }

    /**
     * Geting path after Document root Path , this will be shown after
     *     Document Root path only
     * @param  string $path the path to be clean, make sure to get right values
     *                      checking path (path) must be check on after root path
     * @return string       if match path with root path
     */
    public static function getPathAfterDocumentRoot($path)
    {
        $root = static::documentRoot();
        $path = rtrim(self::cleanSlashed($path), '/');
        if (strpos($path, $root) !== false) {
            $path =  substr($path, strlen($root));
            return '/'.trim($path, '/');
        }

        return null;
    }
}
