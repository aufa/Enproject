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

/**
 * Add aliases Magic class calling instance
 * Aufa\Enproject\{CryptographyClass} subclass of singleton
 */
class Cryptography extends Singleton
{
    /**
     * Magic Method Geting properties
     *
     * @param  string $appname property key name
     * @return object       Cryptography instance if exists
     */
    public function __get($appname)
    {
        return self::get($appname);
    }

    /**
     * Get application instantly
     *
     * @param  string $appname  the application key name
     * @return object           Cryptography instance exists
     */
    public static function get($appname)
    {
        return Enproject::get("\\Cryptography\\{$appname}");
    }

    /**
     * Get application instantly
     *
     * @param  string $appname  the application key name
     * @param  object $object   Object application
     * @return object           Enproject instance
     */
    public static function set($appname, $object)
    {
        return Enproject::set("\\Cryptography\\{$appname}", $object);
    }
}
