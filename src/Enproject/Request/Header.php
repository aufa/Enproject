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

namespace Aufa\Enproject\Request;

use Aufa\Enproject\Abstracts\Singleton;
use Aufa\Enproject\Collector\Header as Collector;
use Aufa\Enproject\Request\Server;

/**
 * Header Request
 */
class Header extends Singleton
{
    /**
     * ServerCollector
     * @var object
     */
    protected static $Collector;

    /**
     * PHP5 Constructor
     */
    public function __construct()
    {
        parent::__construct();
        static::collector();
    }

    /**
     * Instance Collector
     *
     * @return  object Collector
     */
    public static function collector()
    {
        if (!self::$Collector instanceof Collector) {
            self::$Collector = new Collector(Server::getHeaders());
        }
        return self::$Collector;
    }

    /* --------------------------------------------------------------------------------*
     |                                 Overloading                                     |
     |---------------------------------------------------------------------------------|
     */

    /**
     * Handle Call Static method for curent class
     * This magic method handle by Internal of PHP
     *
     * @param string $method    the method name
     * @param array  $arguments the arguments
     */
    public static function __callStatic($method, $arguments)
    {
        if (method_exists(self::collector(), $method)) {
            return call_user_func_array(array(self::collector(), $method), $arguments);
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
        //Trigger appropriate error
        trigger_error(' Call to undefined method '
            . $callee['class']
            . $callee['type']
            . $callee['function']
            .'() in <b>'.$callee['file'].'</b> on line <b>'.$callee['line']."</b><br />\n", E_USER_ERROR);
    }

    /**
     * Handle Call method for curent class
     * This magic method handle by Internal of PHP
     *
     * @param string $method    the method name
     * @param array  $arguments the arguments
     */
    public function __call($method, $arguments)
    {
        if (method_exists(self::collector(), $method)) {
            return call_user_func_array(array(self::collector(), $method), $arguments);
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
        //Trigger appropriate error
        trigger_error(' Call to undefined method '
            . $callee['class']
            . $callee['type']
            . $callee['function']
            .'() in <b>'.$callee['file'].'</b> on line <b>'.$callee['line']."</b><br />\n", E_USER_ERROR);
    }
}
