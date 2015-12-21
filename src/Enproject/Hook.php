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
 * This Class Running like as a WordPress Hook
 * @license  Follow WordPress GPLv3 or later License
 * @see  {@link: https://wordpress.org/license} for more related WordPress License
 */
class Hook extends Singleton
{
    /* --------------------------------------------------------------------------------*
     |                              Class Properties                                   |
     |---------------------------------------------------------------------------------|
     */

    /**
     * Merged Hooks Records
     * @var array
     */
    protected $x_merged = array();

    /**
     * Current Hooks Record
     * @var array
     */
    protected $x_current = array();

    /**
     * Actions Records
     * @var array
     */
    protected $x_actions = array();

    /**
     * Filter Records
     * @var array
     */
    protected $x_filters = array();

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
    }

    /**
     * Add Hooks Function
     *
     * @param string    $hookName            Hook Name
     * @param string    $function_to_replace Function to replace
     * @param Callable  $callable            Callable
     * @param integer   $priority            priority
     * @param integer   $accepted_args       num count of accepted args / parameter
     * @param boolean   $append              true if want to create new / append if not exists
     */
    public static function add($hookName, $callable, $priority = 10, $accepted_args = 1, $append = true)
    {
        $instance = static::singleton();

        $hookName = static::sanitizeKey($hookName);
        if (!$hookName) {
            throw new \Exception("Invalid Hook Name Specified", E_ERROR);
        }
        // check append and has callable
        if (static::has($hookName, $callable) && ! $append) {
            return false;
        }

        $x_id = $instance->createUniqueIdx($hookName, $callable, $priority);
        $hook_list[$hookName][$priority][$x_id] = array(
            'function' => $callable,
            'accepted_args' => $accepted_args
        );
        $instance->x_filters = array_merge($instance->x_filters, $hook_list);
        unset($instance->x_merged[$hookName]);
        return true;
    }

    /**
     * Check if hook name exists
     *
     * @param  string $hookName              Hook name
     * @param  string $function_to_check     Specially Functions on Hook
     * @return boolean                       true if has hook
     */
    public static function has($hookName, $function_to_check = false)
    {
        $instance = static::singleton();
        $hookName = static::sanitizeKey($hookName);
        if (!$hookName || !isset($instance->x_filters[$hookName])) {
            return false;
        }
        // Don't reset the internal array pointer
        $has    = !empty($instance->x_filters[$hookName]);
        // Make sure at least one priority has a filter callback
        if ($has) {
            $exists = false;
            foreach ($instance->x_filters[$hookName] as $callbacks) {
                if (! empty($callbacks)) {
                    $exists = true;
                    break;
                }
            }

            if (! $exists) {
                $has = false;
            }
        }
        // recheck
        if (false === $function_to_check || false === $has ||
            ! $x_id = $instance->createUniqueIdx($hookName, $function_to_check, false)
        ) {
            return false;
        }

        foreach (array_keys($instance->x_filters[$hookName]) as $priority) {
            if (isset($instance->x_filters[$hookName][$priority][$x_id])) {
                return $priority;
            }
        }

        return false;
    }

    /**
     * Appending Hooks Function
     *
     * @param  string    $hookName            Hook Name
     * @param  string    $function_to_replace Function to replace
     * @param  Callable  $callable            Callable
     * @param  integer   $priority            priority
     * @param  integer   $accepted_args       num count of accepted args / parameter
     * @param  boolean   $create              true if want to create new if not exists
     */
    public static function append($hookName, $callable, $priority = 10, $accepted_args = 1, $create = true)
    {
        return static::add($hookName, $callable, $priority, $accepted_args, true);
    }

    /**
     * Applying Hooks for replaceable and returning as $value param
     *
     * @param  string $hookName Hook Name replaceable
     * @param  mixed $value     returning value
     */
    public static function apply($hookName, $value)
    {
        $instance = static::singleton();
        $hookName = static::sanitizeKey($hookName);
        if (!$hookName) {
            return $value;
        }

        $args = array();
        // Do 'all' actions first.
        if (isset($hookName->x_filters['all'])) {
            $hookName->x_current[] = $hookName;
            $args = func_get_args();
            $hookName->callAllHook($args);
        }

        if (! isset($hookName->x_filters[$hookName])) {
            if (isset($hookName->x_filters['all'])) {
                array_pop($hookName->x_current);
            }
            return $value;
        }

        if (! isset($hookName->x_filters['all'])) {
            $hookName->x_current[] = $hookName;
        }

        // Sort.
        if (!isset($hookName->x_merged[$hookName])) {
            ksort($hookName->x_filters[$hookName]);
            $hookName->x_merged[$hookName] = true;
        }

        reset($hookName->x_filters[$hookName]);
        if (empty($args)) {
            $args = func_get_args();
        }
        do {
            foreach ((array) current($hookName->x_filters[$hookName]) as $the_) {
                if (!is_null($the_['function'])) {
                    $args[1] = $value;
                    $value = call_user_func_array(
                        $the_['function'],
                        array_slice($args, 1, (int) $the_['accepted_args'])
                    );
                }
            }
        } while (next($hookName->x_filters[$hookName]) !== false);

        array_pop($hookName->x_current);
        return $value;
    }

    /**
     * Do action hook now
     *
     * @param  string $hookName Hook Name
     * @param  string $arg      the arguments for next parameter
     */
    public static function doAction($hookName, $arg = '')
    {
        $instance = static::singleton();
        $hookName = static::sanitizeKey($hookName);
        if (!$hookName) {
            return false;
        }
        if (! isset($instance->x_actions[$hookName])) {
            $instance->x_actions[$hookName] = 1;
        } else {
            $instance->x_actions[$hookName]++;
        }

        // Do 'all' actions first
        if (isset($instance->x_filters['all'])) {
            $instance->x_current[] = $hookName;
            $all_args = func_get_args();
            $instance->callAllHook($all_args);
        }

        if (!isset($instance->x_filters[$hookName])) {
            if (isset($instance->x_filters['all'])) {
                array_pop($instance->x_current);
            }
            return;
        }

        if (!isset($instance->x_filters['all'])) {
            $instance->x_current[] = $hookName;
        }

        $args = array();
        if (is_array($arg) && 1 == count($arg) && isset($arg[0]) && is_object($arg[0])) {
            // array(&$this)
            $args[] =& $arg[0];
        } else {
            $args[] = $arg;
        }

        for ($a = 2, $num = func_num_args(); $a < $num; $a++) {
            $args[] = func_get_arg($a);
        }
        // Sort
        if (!isset($instance->x_merged[$hookName])) {
            ksort($instance->x_filters[$hookName]);
            $instance->x_merged[$hookName] = true;
        }
        reset($instance->x_filters[$hookName]);
        do {
            foreach ((array) current($instance->x_filters[$hookName]) as $the_) {
                if (!is_null($the_['function'])) {
                    call_user_func_array($the_['function'], array_slice($args, 0, (int) $the_['accepted_args']));
                }
            }
        } while (next($instance->x_filters[$hookName]) !== false);
        array_pop($instance->x_current);
    }

    /**
     * Do action hook now
     *
     * @param  string $hookName Hook Name
     * @param  string $arg      the arguments for next parameter
     */
    public static function doHook($hookName)
    {
        $instance = static::singleton();
        return call_user_func_array(array($instance, 'doAction'), func_get_args());
    }

    /**
     * Replace Hooks Function
     * @param  string    $hookName            Hook Name
     * @param  string    $function_to_replace Function to replace
     * @param  Callable  $callable            Callable
     * @param  integer   $priority            priority
     * @param  integer   $accepted_args       num count of accepted args / parameter
     * @param  boolean   $create              true if want to create new if not exists
     */
    public static function replace(
        $hookName,
        $function_to_replace,
        $callable,
        $priority = 10,
        $accepted_args = 1,
        $create = true
    ) {
        $instance = static::singleton();
        $hookName = static::sanitizeKey($hookName);
        if (!$hookName) {
            throw new \Exception("Invalid Hook Name Specified", E_ERROR);
        }
        if ($this->has($hookName)) {
            $this->removeHook($hookName, $function_to_replace);
            return $this->add($hookName, $callable, $priority, $accepted_args, true);
        }
        if ($create) {
            return $this->add($hookName, $callable, $priority, $accepted_args, true);
        }
    }

    /**
     * Removing Hooks
     *
     * @param  string  $hookName           Hook Name
     * @param  string  $function_to_remove functions that to remove from determine $hookName
     * @param  integer $priority           priority
     * @return boolean                     true if has removed
     */
    public static function removeHook($hookName, $function_to_remove, $priority = 10)
    {
        $instance = static::singleton();
        $hookName = static::sanitizeKey($hookName);
        if (!$hookName) {
            return false;
        }
        $function_to_remove = $this->createUniqueIdx($hookName, $function_to_remove, $priority);
        $r = isset($instance->x_filters[$hookName][$priority][$function_to_remove]);
        if (true === $r) {
            unset($instance->x_filters[$hookName][$priority][$function_to_remove]);
            if (empty($instance->x_filters[$hookName][$priority])) {
                unset($instance->x_filters[$hookName][$priority]);
            }
            if (empty($instance->x_filters[$hookName])) {
                $instance->x_filters[$hookName] = array();
            }
            unset($instance->x_merged[$hookName]);
        }

        return $r;
    }

    /**
     * Remove all of the hooks from a filter.
     *
     * @param string   $hookName    The filter to remove hooks from.
     * @param int|bool $priority    Optional. The priority number to remove. Default false.
     * @return true                 True when finished.
     */
    public static function removeAllFilters($hookName, $priority = false)
    {
        $instance = static::singleton();
        if (isset($instance->x_filters[$hookName])) {
            if (false === $priority) {
                $instance->x_filters[$hookName] = array();
            } elseif (isset($instance->x_filters[$hookName][$priority])) {
                $instance->x_filters[$hookName][$priority] = array();
            }
        }
        unset($instance->x_merged[$hookName]);
        return true;
    }

    /**
     * Aliases
     */
    public static function removeAllFilter($hookName, $priority = false)
    {
        return static::removeAllFilters($hookName, $priority);
    }

    /**
     * Aliases
     */
    public static function removeAllActions($hookName, $priority = false)
    {
        return static::removeAllFilters($hookName, $priority);
    }

    /**
     * Aliases
     */
    public static function removeAllAction($hookName, $priority = false)
    {
        return static::removeAllFilters($hookName, $priority);
    }

    /**
     * Current position
     * @return string functions
     */
    public static function current()
    {
        $instance = static::singleton();
        return end($instance->x_current);
    }

    /**
     * Aliases $this::has()
     * @param  Hook $hookName       Hook name
     * @param  function_to_check]   Specially Functions on Hook
     * @return boolean              true if has hook
     */
    public static function exist($hookName, $function_to_check = false)
    {
        return static::has($hookName, $function_to_check = false);
    }

    /**
     * Count all existences Hook
     *
     * @param  string $hookName Hook name
     * @return integer          Hooks Count
     */
    public static function countHook($hookName)
    {
        $instance = static::singleton();
        $hookName = static::sanitizeKey($hookName);
        if (!$hookName || !isset($instance->x_filters[$hookName])) {
            return false;
        }
        return count((array) $instance->x_filters[$hookName]);
    }

    /**
     * Check if hook has doing
     *
     * @param  string $hookName Hook name
     * @return boolean           true if has doing
     */
    public static function doingHook($hookName = null)
    {
        $instance = static::singleton();
        if (null === $hookName) {
            return ! empty($instance->x_current);
        }

        $hookName = static::sanitizeKey($hookName);
        return in_array($hookName, $instance->x_current);
    }

    /**
     * Alias $this::doingHook();
     */
    public static function doingAction($hookName = false)
    {
        return static::doingHook($hookName);
    }

    /**
     * Check if action hook as execute
     *
     * @param  string $hookName Hook Name
     * @return integer          Count of hook action if has did action
     */
    public static function didAction($hookName)
    {
        $instance = static::singleton();
        $hookName = static::sanitizeKey($hookName);
        if (!$hookName || ! isset($instance->x_actions[$hookName])) {
            return 0;
        }

        return $instance->x_actions[$hookName];
    }

    /**
     * Sanitize Hook key
     *
     * @param  string $keyName Keyname
     * @return string          keyname Fix
     */
    public static function sanitizeKey($keyName)
    {
        if (is_array($keyName) || is_object($keyName)) {
            return null;
        }
        return strtolower(trim($keyName));
    }

    /**
     * Creating Unqiue Id For Hooks
     * @access private
     *
     * @param  string   $hookname the hookname group
     * @param  string   $function the Function of hook
     * @param  integer  $priority priority
     */
    protected function createUniqueIdx($hookName, $function, $priority)
    {
        static $x_count = 0;
        if (is_string($function)) {
            return $function;
        }
        if (is_object($function)) {
            // Closures are currently implemented as objects
            $function = array($function, '');
        } elseif (!is_array($function)) {
            $function = array($function);
        }
        if (is_object($function[0])) {
            // Object Class Calling
            if (function_exists('spl_object_hash')) {
                return spl_object_hash($function[0]) . $function[1];
            } else {
                $object_x_id = get_class($function[0]).$function[1];
                if (!isset($function[0]->x_id)) {
                    if (false === $priority) {
                        return false;
                    }
                    $object_x_id .= isset($this->x_filters[$hookName][$priority])
                        ? count((array)$this->x_filters[$hookName][$priority])
                        : $x_count;
                    $function[0]->x_id = $x_count;
                    $x_count++;
                } else {
                    $object_x_id .= $function[0]->x_id;
                }
                return $object_x_id;
            }
        } elseif (is_string($function[0])) {
            // Static Calling
            return $function[0] . '::' . $function[1];
        }
    }

    /**
     * Call the 'all' hook, which will process the functions hooked into it.
     *
     * The 'all' hook passes all of the arguments or parameters that were used for
     * the hook, which this function was called for.
     *
     * This function is used internally for apply_filters(), do_action(), and
     * do_action_ref_array() and is not meant to be used from outside those
     * functions. This function does not check for the existence of the all hook, so
     * it will fail unless the all hook exists prior to this function call.
     *
     * @access private
     * @param array $args The collected parameters from the hook that was called.
     */
    private function callAllHook($args)
    {
        if (!isset($this->x_filters['all'])) {
            return false;
        }
        reset($this->x_filters['all']);
        do {
            foreach ((array) current($this->x_filters['all']) as $the_) {
                if (!is_null($the_['function'])) {
                    call_user_func_array($the_['function'], $args);
                }
            }
        } while (next($this->x_filters['all']) !== false);
    }

    /**
     * Overloading __tostring()
     * @return string
     */
    public function __toString()
    {
        return App::singleton()->__toString();
    }
}
