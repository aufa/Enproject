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

use ArrayAccess;
use ArrayIterator;
use Countable;
use IteratorAggregate;

/**
 * Collector
 *     Get all data copied from \Klein\DataCollection\DataCollection
 */
class Collector implements IteratorAggregate, ArrayAccess, Countable
{
    /**
     * Collection of data attributes
     *
     * @type array
     */
    protected $x_attributes = array();

    /**
     * Constructor
     *
     * @param array $attributes The data attributes of this collection
     */
    public function __construct(array $attributes = array())
    {
        $this->x_attributes = $attributes;
    }

    /**
     * Returns all of the key names in the collection
     *
     * If an optional mask array is passed, this only
     * returns the keys that match the mask
     *
     * @param array $mask               The parameter mask array
     * @param boolean $fill_with_nulls  Whether or not to fill the returned array with
     *  values to match the given mask, even if they don't exist in the collection
     * @return array
     */
    public function keys($mask = null, $fill_with_nulls = true)
    {
        if (null !== $mask) {
            // Support a more "magical" call
            if (!is_array($mask)) {
                $mask = func_get_args();
            }

            /*
             * Make sure that the returned array has at least the values
             * passed into the mask, since the user will expect them to exist
             */
            if ($fill_with_nulls) {
                $keys = $mask;
            } else {
                $keys = array();
            }

            /*
             * Remove all of the values from the keys
             * that aren't in the passed mask
             */
            return array_intersect(
                array_keys($this->x_attributes),
                $mask
            ) + $keys;
        }

        return array_keys($this->x_attributes);
    }

    /**
     * Returns all of the attributes in the collection
     *
     * If an optional mask array is passed, this only
     * returns the keys that match the mask
     *
     * @param array $mask               The parameter mask array
     * @param boolean $fill_with_nulls  Whether or not to fill the returned array with
     *  values to match the given mask, even if they don't exist in the collection
     * @return array
     */
    public function all($mask = null, $fill_with_nulls = true)
    {
        if (null !== $mask) {
            // Support a more "magical" call
            if (!is_array($mask)) {
                $mask = func_get_args();
            }

            /*
             * Make sure that each key in the mask has at least a
             * null value, since the user will expect the key to exist
             */
            if ($fill_with_nulls) {
                $attributes = array_fill_keys($mask, null);
            } else {
                $attributes = array();
            }

            /*
             * Remove all of the keys from the attributes
             * that aren't in the passed mask
             */
            return array_intersect_key(
                $this->x_attributes,
                array_flip($mask)
            ) + $attributes;
        }

        return $this->x_attributes;
    }

    /**
     * Return an attribute of the collection
     *
     * Return a default value if the key doesn't exist
     *
     * @param string $key           The name of the parameter to return
     * @param mixed  $default_val   The default value of the parameter if it contains no value
     * @return mixed
     */
    public function get($key, $default_val = null)
    {
        if (isset($this->x_attributes[$key])) {
            return $this->x_attributes[$key];
        }

        return $default_val;
    }

    /**
     * Set an attribute of the collection
     *
     * @param string $key   The name of the parameter to set
     * @param mixed  $value The value of the parameter to set
     * @return DataCollection
     */
    public function set($key, $value)
    {
        $this->x_attributes[$key] = $value;

        return $this;
    }

    /**
     * Replace the collection's attributes
     *
     * @param array $attributes The attributes to replace the collection's with
     * @return DataCollection
     */
    public function replace(array $attributes = array())
    {
        $this->x_attributes = $attributes;

        return $this;
    }

    /**
     * Merge attributes with the collection's attributes
     *
     * Optionally allows a second boolean parameter to merge the attributes
     * into the collection in a "hard" manner, using the "array_replace"
     * method instead of the usual "array_merge" method
     *
     * @param array $attributes The attributes to merge into the collection
     * @param boolean $hard     Whether or not to make the merge "hard"
     * @return DataCollection
     */
    public function merge(array $attributes = array(), $hard = false)
    {
        // Don't waste our time with an "array_merge" call if the array is empty
        if (!empty($attributes)) {
            // Hard merge?
            if ($hard) {
                $this->x_attributes = array_replace(
                    $this->x_attributes,
                    $attributes
                );
            } else {
                $this->x_attributes = array_merge(
                    $this->x_attributes,
                    $attributes
                );
            }
        }

        return $this;
    }

    /**
     * See if an attribute exists in the collection
     *
     * @param string $key   The name of the parameter
     * @return boolean
     */
    public function exists($key)
    {
        // Don't use "isset", since it returns false for null values
        return array_key_exists($key, $this->x_attributes);
    }

    /**
     * Remove an attribute from the collection
     *
     * @param string $key   The name of the parameter
     * @return void
     */
    public function remove($key)
    {
        unset($this->x_attributes[$key]);
    }

    /**
     * Clear the collection's contents
     *
     * Semantic alias of a no-argument `$this->replace` call
     *
     * @return DataCollection
     */
    public function clear()
    {
        return $this->replace();
    }

    /**
     * Check if the collection is empty
     *
     * @return boolean
     */
    public function isEmpty()
    {
        return empty($this->x_attributes);
    }

    /**
     * A quick convenience method to get an empty clone of the
     * collection. Great for dependency injection. :)
     *
     * @return DataCollection
     */
    public function cloneEmpty()
    {
        $clone = clone $this;
        $clone->clear();

        return $clone;
    }

    /* --------------------------------------------------------------------------------*
     |                                   Overloading                                   |
     |---------------------------------------------------------------------------------|
     */

    /**
     * Magic "__get" method
     *
     * Allows the ability to arbitrarily request an attribute from
     * this instance while treating it as an instance property
     *
     * @see get()
     * @param string $key   The name of the parameter to return
     * @return mixed
     */
    public function __get($key)
    {
        return $this->get($key);
    }

    /**
     * Magic "__set" method
     *
     * Allows the ability to arbitrarily set an attribute from
     * this instance while treating it as an instance property
     *
     * @see set()
     * @param string $key   The name of the parameter to set
     * @param mixed  $value The value of the parameter to set
     * @return void
     */
    public function __set($key, $value)
    {
        $this->set($key, $value);
    }

    /**
     * Magic "__isset" method
     *
     * Allows the ability to arbitrarily check the existence of an attribute
     * from this instance while treating it as an instance property
     *
     * @see exists()
     * @param string $key   The name of the parameter
     * @return boolean
     */
    public function __isset($key)
    {
        return $this->exists($key);
    }

    /**
     * Magic "__unset" method
     *
     * Allows the ability to arbitrarily remove an attribute from
     * this instance while treating it as an instance property
     *
     * @see remove()
     * @param string $key   The name of the parameter
     * @return void
     */
    public function __unset($key)
    {
        $this->remove($key);
    }

    /* --------------------------------------------------------------------------------*
     |                                ArrayIterator                                    |
     |---------------------------------------------------------------------------------|
     */
    /**
     * Get the aggregate iterator
     *
     * IteratorAggregate interface required method
     *
     * @see \IteratorAggregate::getIterator()
     * @return ArrayIterator
     */
    public function getIterator()
    {
        return new ArrayIterator($this->x_attributes);
    }

    /* --------------------------------------------------------------------------------*
     |                                   ArrayAccess                                   |
     |---------------------------------------------------------------------------------|
     */

    /**
     * Get an attribute via array syntax
     *
     * Allows the access of attributes of this instance while treating it like an array
     *
     * @see \ArrayAccess::offsetGet()
     * @see get()
     * @param string $key   The name of the parameter to return
     * @return mixed
     */
    public function offsetGet($key)
    {
        return $this->get($key);
    }

    /**
     * Set an attribute via array syntax
     *
     * Allows the access of attributes of this instance while treating it like an array
     *
     * @see \ArrayAccess::offsetSet()
     * @see set()
     * @param string $key   The name of the parameter to set
     * @param mixed  $value The value of the parameter to set
     * @return void
     */
    public function offsetSet($key, $value)
    {
        $this->set($key, $value);
    }

    /**
     * Check existence an attribute via array syntax
     *
     * Allows the access of attributes of this instance while treating it like an array
     *
     * @see \ArrayAccess::offsetExists()
     * @see exists()
     * @param string $key   The name of the parameter
     * @return boolean
     */
    public function offsetExists($key)
    {
        return $this->exists($key);
    }

    /**
     * Remove an attribute via array syntax
     *
     * Allows the access of attributes of this instance while treating it like an array
     *
     * @see \ArrayAccess::offsetUnset()
     * @see remove()
     * @param string $key   The name of the parameter
     * @return void
     */
    public function offsetUnset($key)
    {
        $this->remove($key);
    }

    /* --------------------------------------------------------------------------------*
     |                                    Countable                                    |
     |---------------------------------------------------------------------------------|
     */

    /**
     * Count the attributes via a simple "count" call
     *
     * Allows the use of the "count" function (or any internal counters)
     * to simply count the number of attributes in the collection.
     *
     * @see \Countable::count()
     * @return int
     */
    public function count()
    {
        return count($this->x_attributes);
    }
}
