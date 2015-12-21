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
use ArrayAccess;
use ArrayIterator;
use Countable;
use IteratorAggregate;

/**
 * Final Config Class
 * Using final as unExtendable class to prevent
 * Another CLass being override Method
 * Save Any configuration use singleton
 */
final class Config extends Singleton implements ArrayAccess, Countable, IteratorAggregate
{
    /* --------------------------------------------------------------------------------*
     |                              Class Properties                                   |
     |---------------------------------------------------------------------------------|
     */

    /**
     * Config records
     * @var array
     */
    protected $x_config = array();

    /**
     * Protected Config records
     * @var array
     */
    protected $x_protected_key = array();

    /**
     * Protected Prevent Config records
     * @var array
     */
    protected $x_prevent_protected_key = array();

    /**
     * Record the last key set
     * @var string
     */
    protected $x_last_key;

    /* --------------------------------------------------------------------------------*
     |                                Class Method                                     |
     |---------------------------------------------------------------------------------|
     */

    /**
     * PHP 5 Constructor
     */
    public function __construct($config = array())
    {
        // doing isntance of current object
        parent::__construct();
        static::set($config);
    }

    /**
     * Instance singleton COnfig
     * on call Config::Config([array $config])
     *
     * @return object config instance
     */
    public static function config($config = array())
    {
        $class = __CLASS__;
        if (!isset(static::$x_instance_arr[$class]) || !static::$x_instance_arr[$class] instanceof $class) {
            static::$x_instance_arr[$class] = new static($config);
        }

        return static::$x_instance_arr[$class];
    }

    /**
     * Set Configuration if not exists
     * @param  string $name  key name for configuration
     * @param  mixed  $value value for config
     * @return object        Config class
     */
    public static function set($name, $value = null)
    {
        if (is_array($name)) {
            foreach ($name as $key => $val) {
                static::set($key, $val);
            }
        } else {
            if (!static::has($name)) {
                static::replace($name, $value);
                static::singleton()->x_last_key = $name;
            }
        }
        return static::singleton();
    }

    /**
     * Replace configuration / or set if not exist
     * @param  string $name  key name for configuration
     * @param  mixed  $value value for config
     * @return object        Config class
     */
    public static function replace($name, $value = null)
    {
        $instance = static::singleton();
        if (is_array($name)) {
            array_map(array($instance, 'replace'), $name);
        } elseif (is_string($name) || is_int($name)) {
            $instance->x_config[$name] = $value;
            $instance->x_last_key = $name;
        }
        return $instance;
    }

    /**
     * Get configuration value
     * @param  string $name key name for configuration
     * @param  mixed  $default default return value if config nt exist
     */
    public static function get($name, $default = null)
    {
        return static::has($name) ? static::singleton()->x_config[$name] : $default;
    }

    /**
     * Check if Configuration exist
     *
     * @param  string  $name Config Name
     * @return boolean       true if exist
     */
    public static function has($name)
    {
        if (is_string($name) || is_int($name)) {
            return array_key_exists($name, static::getAll());
        }
        return null;
    }

    /**
     * Remove Config if has no protected
     *
     * @param  string $name key name for configuration
     * @return bool         true if has been unset
     */
    public static function remove($name)
    {
        $instance = static::singleton();
        if (static::has($name) && !in_array($name, $instance->x_protected_key)) {
            $config = $instance->x_config;
            unset($config[$name]);
            $instance->x_config = $config;
            return true;
        }
        return false;
    }

    /**
     * Alias self::protect() Protect Configuration  by key name config
     *
     * @param  string|array $name key name for configuration / null if using last key
     * @return object       Config class
     */
    public static function protectConfig($name = null)
    {
        return self::protect($name);
    }

    /**
     * Alias self::preventProtect() Prevent Protect Configuration  by key name config
     *
     * @param  string|array $name key name for configuration / null if using last key
     * @return object       Config class
     */
    public static function prevent($name = null)
    {
        return self::preventProtect($name);
    }

    /**
     * Get all configs record
     *
     * @return array configuration lists
     */
    public static function getAll()
    {
        return (array) static::singleton()->x_config;
    }

    /**
     * Get all configs record (alias static::getAll())
     *
     * @return array configuration lists
     */
    public static function all()
    {
        return static::getAll();
    }

    /**
     * Protect Configuration  by key name config
     *
     * @param  string|array $name key name for configuration / null if using last key
     * @return object       Config class
     */
    public static function protect($name = null)
    {
        $instance = static::singleton();
        if ($name === null) {
            // if last key is null end here
            if ($instance->x_last_key === null) {
                return $instance;
            }
            $name = $instance->x_last_key;
        }

        if ((is_string($name) || is_int($name))
            && self::has($name)
            && ! in_array($name, $instance->x_protected_key)
            && ! in_array($name, $instance->x_prevent_protected_key)
        ) {
            $instance->x_protected_key[] = $name;
        }

        /**
         * if config protect is array
         */
        if (is_array($name)) {
            foreach ($name as $value) {
                // doing array protected
                self::protect($value);
            }
        }

        // reset
        ($name === $instance->x_last_key) && $instance->x_last_key = null;

        return $instance;
    }

    /**
     * Prevent Protect Configuration by key name config
     *     If has been protected this will has no effect
     * @param  string|array $name key name for configuration / null if using last key
     * @return object       Config class
     */
    public static function preventProtect($name = null)
    {
        $instance = static::singleton();
        if ($name === null) {
            // if last key is null end here
            if ($instance->x_last_key === null) {
                return $instance;
            }
            $name = $instance->x_last_key;
        }

        if ((is_string($name) || is_int($name))
            && self::has($name)
            && ! in_array($name, $instance->x_protected_key)
            && ! in_array($name, $instance->x_prevent_protected_key)
        ) {
            $instance->x_prevent_protected_key[] = $name;
        }

        /**
         * if config protect is array
         */
        if (is_array($name)) {
            foreach ($name as $value) {
                // doing array protected
                self::preventProtect($value);
            }
        }

        // reset
        ($name === $instance->x_last_key) && $instance->x_last_key = null;

        return $instance;
    }

    /* --------------------------------------------------------------------------------*
     |                       Magic Method Overloading                                  |
     |---------------------------------------------------------------------------------|
     */

    /**
     * Magic Method __get, get value of object property
     *
     * @param string $key property variable
     */
    public function __get($key)
    {
        return static::get($key);
    }

    /**
     * Magic Method __set, set the object property
     *
     * @param string $key property variable
     * @param string $value property value
     */
    public function __set($key, $value)
    {
        static::replace($key, $value);
    }

    /**
     * Magic Method __isset
     *
     * @param  string  $key if has property
     * @return boolean      true if exists
     */
    public function __isset($key)
    {
        return static::has($key);
    }

    /**
     * Magic Method __unset, unset the property
     *
     * @param string $key property variable
     */
    public function __unset($key)
    {
        static::remove($key);
    }

    /* --------------------------------------------------------------------------------*
     |                               ArrayAccess                                       |
     |---------------------------------------------------------------------------------|
     */

    /**
     * Array Access: Offset Exists
     */
    public function offsetExists($offset)
    {
        return static::has($offset);
    }

    /**
     * Array Access: Offset Get
     */
    public function offsetGet($offset)
    {
        return static::get($offset);
    }

    /**
     * Array Access: Offset Set
     */
    public function offsetSet($offset, $value)
    {
        static::replace($offset, $value);
    }

    /**
     * Array Access: Offset Unset
     */
    public function offsetUnset($offset)
    {
        static::remove($offset);
    }

    /* --------------------------------------------------------------------------------*
     |                                Countable                                        |
     |---------------------------------------------------------------------------------|
     */

    /**
     * Countable: Count
     */
    public function count()
    {
        return count(static::getAll());
    }

    /* --------------------------------------------------------------------------------*
     |                               IteratorAgregate                                  |
     |---------------------------------------------------------------------------------|
     */

    /**
     * Iterator
     */
    public function getIterator()
    {
        return new ArrayIterator(static::getAll());
    }
}
