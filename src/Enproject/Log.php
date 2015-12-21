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
use Aufa\Enproject\Helper\LogWriter;

/**
 * Error Logger CLass logs
 */
class Log extends Singleton
{
    /* --------------------------------------------------------------------------------*
     |                              Class Properties                                   |
     |---------------------------------------------------------------------------------|
     */

    /**
     * error records
     * @var array
     */
    protected $x_error_logs    = array();

    /**
     * Error Records that notice and should not to be show
     * @var array
     */
    protected $x_error_logs_no_records    = array();
    
    /**
     * Info log Records
     * @var array
     */
    protected $x_info_logs    = array(
    );

    /**
     * Log writer
     * @var  object log writer
     */
    protected $x_write = null;

    /**
     * Error Type In string
     * @var array
     */
    protected $x_err_type_str = array(
        ErrorHandler::E_ERROR             => 'ERROR',
        ErrorHandler::E_RECOVERABLE_ERROR => 'RECOVERABLE_ERROR',
        ErrorHandler::E_WARNING           => 'WARNING',
        ErrorHandler::E_PARSE             => 'PARSE',
        ErrorHandler::E_NOTICE            => 'NOTICE',
        ErrorHandler::E_STRICT            => 'STRICT',
        ErrorHandler::E_DEPRECATED        => 'DEPRECATED',
        ErrorHandler::E_CORE_ERROR        => 'CORE_ERROR',
        ErrorHandler::E_CORE_WARNING      => 'CORE_WARNING',
        ErrorHandler::E_COMPILE_ERROR     => 'COMPILE_ERROR',
        ErrorHandler::E_COMPILE_WARNING   => 'COMPILE_WARNING',
        ErrorHandler::E_USER_ERROR        => 'USER_ERROR',
        ErrorHandler::E_USER_WARNING      => 'USER_WARNING',
        ErrorHandler::E_USER_NOTICE       => 'USER_NOTICE',
        ErrorHandler::E_USER_DEPRECATED   => 'USER_DEPRECATED',
        E_USER_ZERO                       => 'USER_INFO', // user informational
        E_USER_DEBUG                      => 'USER_DEBUG', // user debug
    );

    /**
     * PHP5 Constructor
     */
    public function __construct()
    {
        parent::__construct();
        // set writer
        self::setWriter(new LogWriter());
    }

    /**
     * Set Writer
     *
     * @param object $writer the log writer class
     */
    public static function setWriter($writer)
    {
        if (is_string($writer) && class_exists($writer)) {
            $writer = new $writer;
        }
        $instance = static::singleton();
        if (is_object($writer) && method_exists($writer, 'write')) {
            $instance->x_writer = $writer;
        }
    }

    /* --------------------------------------------------------------------------------*
     |                                Error Recorder                                   |
     |---------------------------------------------------------------------------------|
     */

    /**
     * Write logs
     *
     * @param  array  $log error logs
     */
    public static function addError(array $log = array())
    {
        $instance = static::singleton();
        /**
         * Check Log and must be valid about logs
         */
        if ($instance->isLogError($log) && isset($instance->x_err_type_str[$log['type']])) {
            $log['type_string'] = $instance->x_err_type_str[$log['type']];
            $instance->x_error_logs[] = $log;
            static::write($log);
            return true;
        }
    }

    /**
     * Log error NOTICE and error_reporting(0)
     *
     * @param  array    $log error logs
     * @return boolean  true if insert valid error
     */
    public static function addNoError(array $log = array())
    {
        $instance = static::singleton();
        if ($instance->isLogError($log)) {
            $log['type_string'] = isset($instance->x_err_type_str[$log['type']])
                ? $instance->x_err_type_str[$log['type']]
                : 'UNKNOWN';
            foreach ($instance->x_error_logs_no_records as $key => $value) {
                if ($value['message'] == $log['message']
                    && $value['file'] == $log['file']
                    && $value['line'] == $log['line']
                ) {
                    // unset
                    unset($instance->x_error_logs_no_records[$key]);
                    // sort it again
                    $instance->x_error_logs_no_records = array_values($instance->x_error_logs_no_records);
                    break;
                }
            }

            $instance->x_error_logs_no_records[] = $log;

            return true;
        }
    }

    /**
     * Write log
     * @param  array  $log logs
     */
    public static function write(array $log)
    {
        $instance = static::singleton();
        if ($instance->isLogError($log)) {
            // set default 0 if unknown
            isset($log['type'], $instance->x_err_type_str)
                || $log['type'] = 0;
            $log['type_string'] = isset($instance->x_err_type_str[$log['type']])
                ? $instance->x_err_type_str[$log['type']]
                : 'UNKNOWN';

            foreach ($instance->x_error_logs as $key => $value) {
                if ($value['message'] == $log['message']
                    && $value['file'] == $log['file']
                    && $value['line'] == $log['line']
                ) {
                    // unset
                    unset($instance->x_error_logs[$key]);
                    // sort it again
                    $instance->x_error_logs = array_values($instance->x_error_logs);
                    break;
                }
            }

            $instance->x_error_logs[] = $log;
            return $instance->x_writer->write($log);
        }
    }

    /**
     * Check if log is valid logs
     * @param  array  $log  log to check
     * @return boolean      true if valid log error
     */
    public function isLogError($log)
    {
        $array_error_key =  array(
            'type',
            'message',
            'file',
            'line',
            'is_fatal',
            'error_exception'
        );
        return (is_array($log) && !empty($log) && ! array_diff($array_error_key, array_keys($log)));
    }

    /**
     * Get error Logs
     *
     * @return array
     */
    public static function getError()
    {
        $instance = static::singleton();
        return $instance->x_error_logs;
    }

    /**
     * Get error Logs no record
     * this is E_NOTICE
     *
     * @return array
     */
    public static function getErrorNoRecord()
    {
        $instance = static::singleton();
        return $instance->x_error_logs_no_records;
    }

    /* --------------------------------------------------------------------------------*
     |                                INFO RECORDER                                    |
     |                                                                                 |
     | this log is functional and suitable for insert information about information on |
     | Your site to logging                                                            |
     | just get info and parse it follow up the keys and unique code default E_DEBUG   |
     |---------------------------------------------------------------------------------|
     */

    public static function add($code = E_DEBUG, $message = null)
    {
    }

    public static function info($code = E_DEBUG, $message = null)
    {
    }

    public static function success($code = E_DEBUG, $message = null)
    {
    }

    public static function notice($code = E_DEBUG, $message = null)
    {
    }

    public static function warning($code = E_DEBUG, $message = null)
    {
    }

    public static function danger($code = E_DEBUG, $message = null)
    {
    }

    public static function invalid($code = E_DEBUG, $message = null)
    {
    }

    public static function deprecated($code = E_DEBUG, $message = null)
    {
    }

    public static function custom($code = E_DEBUG, $message = null)
    {
    }

    public static function get($code = E_DEBUG, $message = null)
    {
    }

    /**
     * Get Info Logs
     *
     * @return array
     */
    public static function getLog()
    {
        $instance = static::singleton();
        return $instance->x_info_logs;
    }

    /**
     * Get Info Logs (alias)
     *
     * @return array
     */
    public static function getLogs()
    {
        return static::getLog();
    }

    /* --------------------------------------------------------------------------------*
     |                                        MISC                                     |
     |---------------------------------------------------------------------------------|
     */

    /**
     * Clear logs error
     */
    public static function clear()
    {
        $instance = static::singleton();
        
        /**
         * Set default Error Logs
         * @var array
         */
        $instance->x_error_logs = array();

        /**
         * Set default Error logs notices
         * @var array
         */
        $instance->x_error_logs_no_records = array();

        /**
         * Set default info logs
         * @var array
         */
        $instance->x_info_logs =  array();
    }

    /**
     * Clear logs error
     */
    public static function clearInfo()
    {
        $instance = static::singleton();
        foreach ($instance->x_info_logs as $key => $value) {
            // reset it
            $instance->x_info_logs[$key] = array();
        }
    }

    /**
     * If has been destruct of class, clear error
     * Magic Method for backwards compatibility
     * clear all logs instead
     */
    public function __destruct()
    {
        static::clear();
    }
}
