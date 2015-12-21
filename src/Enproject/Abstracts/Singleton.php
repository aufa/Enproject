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

namespace Aufa\Enproject\Abstracts;

/**
 * Define another constant for identifier
 */
/**
 * Add {E}ZERO
 */
defined('E_ZERO')       || define('E_ZERO', 0);
defined('E_USER_ZERO')  || define('E_USER_ZERO', 0);
/**
 * use E_NULL
 */
defined('E_NULL')       || define('E_NULL', null);
defined('E_USER_NULL')  || define('E_NULL', null);
/**
 * we are add additional Constant for {E}INFINITE
 */
defined('E_INFINITE')   || define('E_INFINITE', -1); // for Core
defined('E_USER_INFINITE') || define('E_USER_INFINITE', -2); // for User
/**
 * we are add additional Constant for {E}DEBUG
 */
defined('E_DEBUG')      || define('E_DEBUG', -1);
defined('E_USER_DEBUG') || define('E_USER_DEBUG', -2);

/**
 * Singleton abstract class to build Singleton to easier for handle Called class
 */
abstract class Singleton
{
    /* --------------------------------------------------------------------------------*
     |                                 Class Constant                                  |
     |---------------------------------------------------------------------------------|
     */

    /**
     * @const VERSION
     *        Application Version
     */
    const PRODUCTOR = 'Aufa Project';

    /**
     * @const VERSION
     *        Application Version
     */
    const VERSION = '0.1.0';

    /**
     * @const APPNAME
     *        Application name
     */
    const APPNAME = 'Enproject';

    /**
     * @const PACKAGE
     *        PACKAGE Application name
     */
    const PACKAGE = 'aufa/enproject';

    /**
     * @const VERSION
     *        initial version
     */
    const AUTHOR = 'awan <nawa@yahoo.com>';

    /* --------------------------------------------------------------------------------*
     |                                Class Properties                                 |
     |---------------------------------------------------------------------------------|
     */

    /**
     * Instance Singleton Array Per Class Object
     * @var object
     */
    protected static $x_instance_arr = array();

    /**
     * Object instances alias
     * @var array
     */
    protected static $x_instance_alias = array();

    /* --------------------------------------------------------------------------------*
     |                                  Class Method                                   |
     |---------------------------------------------------------------------------------|
     */

    /**
     * PHP 5 Constructor
     */
    public function __construct()
    {
        static::$x_instance_arr[get_called_class()] =& $this;
    }

    /**
     * Instance Current Object / Called Object Class
     * @return object class
     */
    final public static function singleton()
    {
        $called_class = get_called_class();
        /**
         * Fix extendable
         * Check if parent has use children class latest called
         */
        if (isset(static::$x_instance_arr[$called_class])) {
            if (!isset(static::$x_instance_alias[$called_class])) {
                /**
                 * Check each cached Singleton
                 * this will be prevent Duplicate parent as object
                 * and replace parent object as child
                 * Because Singleton always use children to call it instantly
                 */
                foreach (static::$x_instance_arr as $key => $value) {
                    if ($called_class !== $key && is_subclass_of($value, $called_class)) {
                        static::$x_instance_alias[$called_class] = $key;
                        ////////////////////////////////////////////////////////
                        // -- set instance arr called class (deprecated)      //
                        // static::$x_instance_arr[$called_class]   = $value; //
                        ////////////////////////////////////////////////////////
                        // unset it to freed memory & increase performance
                        unset(static::$x_instance_arr[$called_class]);
                        $called_class = $key;
                        break;
                    }
                }
            } else {
                // alias
                $classAlias = static::$x_instance_alias[$called_class];
                // re set
                if (isset(static::$x_instance_arr[$classAlias])) {
                    return static::$x_instance_arr[$classAlias];
                }
            }
        } elseif (!isset(static::$x_instance_alias[$called_class])) {
            // if has no set set it new static
            static::$x_instance_arr[$called_class]   = new static;
        }

        return static::$x_instance_arr[$called_class];
    }

    /**
     * getInstance() aliases singleton
     *
     * @final Singleton() alias
     * @return  Object
     */
    final public static function getInstance()
    {
        return static::singleton();
    }

    /**
     * Trace All current Instance
     *
     * @return array lists object class
     */
    final public static function traceAllIstance()
    {
        $singleton = static::singleton();
        return $singleton::$x_instance_arr;
    }

    /**
     * Deprecated
     */
    // /**
    //  * @access private
    //  */
    // final public static function clearAllState()
    // {
    //     static::$x_instance_arr = array();
    // }

    /* --------------------------------------------------------------------------------*
     |                                  Overloading                                    |
     |---------------------------------------------------------------------------------|
     */

    /**
     * Deprecated
     */
    // /**
    //  * PHP 5 Clone Handle for Backward Compatibility
    //  * Prevent Singletone being clone
    //  */
    // public function __clone()
    // {
    //     trigger_error('Could not clone singleton !', E_USER_ERROR);
    // }

    /**
     * Destruct on end of class proccess -> __destruct for backward Compatibility
     */
    public function __destruct()
    {
    }

    /**
     * PHP 5 backwards Compatibility when echo`ing class
     */
    public function __tostring()
    {
        return static::APPNAME . ' version : '. static::VERSION;
    }

    /**
     * Handle Call Static method for curent class
     * This magic method handle by Internal of PHP
     *
     * @param string $method    the method name
     * @param array  $arguments the arguments
     */
    public static function __callStatic($method, $arguments)
    {
        // if has method static
        if (method_exists(static::singleton(), $method)) {
            return call_user_func_array(array(static::singleton(), $method), $arguments);
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
