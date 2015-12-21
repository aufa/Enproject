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
 * Benchmark record application time
 */
class Benchmark extends Singleton
{
    /* --------------------------------------------------------------------------------*
     |                              Class Properties                                   |
     |---------------------------------------------------------------------------------|
     */

    /**
     * Microtime Benchmark Records
     * @var array
     */
    private $x_microtime = array();

    /**
     * Memory record
     * @var int
     */
    private $x_real_memory;

    /**
     * Memory usage record
     * @var int
     */
    private $x_emalloc_memory;

    /* --------------------------------------------------------------------------------*
     |                                Class Method                                     |
     |---------------------------------------------------------------------------------|
     */

    /**
     * PHP 5 Constructor
     */
    public function __construct()
    {
        $this->x_real_memory    = memory_get_usage(true);
        $this->x_emalloc_memory = memory_get_usage();
    }

    /**
     * Getting Benchmark
     *
     * @param  string $appName key name record
     */
    public static function get($appName = 'app')
    {
        $instance = self::singleton();
        $result = null;
        !empty($instance->x_microtime[$appName]['start'])
            && $result = ($instance->x_microtime[$appName]['end'] - $instance->x_microtime[$appName]['start']);
        return $result;
    }

    /**
     * get current expectation of result benchmark current call execution
     *
     * @param  string $appName key name record
     * @return numeric         seconds time
     */
    public static function currentApp($appName = 'app')
    {
        $instance = self::singleton();
        $result = null;
        !empty($instance->x_microtime[$appName]['start'])
            && $result = (microtime(true) - $instance->x_microtime[$appName]['start']);
        return $result;
    }

    /**
     * Getting microtime
     *
     * @param  string $appName key name record
     */
    public static function getMicrotimeStart($appName = 'app')
    {
        $instance = static::singleton();
        $result = null;
        !empty($instance->x_microtime[$appName]['start'])
            && $result = $instance->x_microtime[$appName]['start'];
        return $result;
    }

    /**
     * Getting microtime last time set
     *
     * @param  string $appName key name record
     */
    public static function getMicrotimeEnd($appName = 'app')
    {
        $instance = static::singleton();
        $result = null;
        !empty($instance->x_microtime[$appName]['end'])
            && $result = $instance->x_microtime[$appName]['end'];
        return $result;
    }

    /**
     * Set Benchmark
     * @param string $appName Applicationkey name
     * @param string $type    start|end of application execute
     */
    public static function set($appName, $type = 'start')
    {
        $instance = static::singleton();
        if (is_string($appName) && is_string($type) && in_array(strtolower(trim($type)), array('start', 'end'))) {
            $type      = strtolower(trim($type));
            $microtime = microtime(true);

            // auto set start and end start is not exists
            if ($type == 'start' && !isset($instance->x_microtime[$appName]['end'])) {
                $instance->x_microtime[$appName]['end'] = $microtime;
            }

            // auto set end and set start is not exists
            if ($type == 'end' && !isset($instance->x_microtime[$appName]['start'])) {
                $instance->x_microtime[$appName]['start'] = $microtime;
            }

            $instance->x_microtime[$appName][$type] = $microtime;
        }

        return $instance;
    }

    /**
     * Getting Current real Memory usage
     *
     * @return integer
     */
    public static function getRealMemory()
    {
        $instance = static::singleton();
        $instance->x_real_memory    = memory_get_usage(true);
        return $instance->x_real_memory;
    }

    /**
     * Get Memory usage
     *
     * @return integer
     */
    public static function getMemory()
    {
        $instance = static::singleton();
        $instance->x_emalloc_memory = memory_get_usage();
        return $instance->x_emalloc_memory;
    }
}
