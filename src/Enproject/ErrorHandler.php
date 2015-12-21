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
use Aufa\Enproject\Helper\Path;
use Aufa\Enproject\Route;

/**
 * Error Handler for system
 * Handling Error
 */
class ErrorHandler extends Singleton
{
    /* --------------------------------------------------------------------------------*
     |                              Class Constant                                     |
     |---------------------------------------------------------------------------------|
     */

    const E_ERROR             = E_ERROR;
    const E_RECOVERABLE_ERROR = E_RECOVERABLE_ERROR;
    const E_WARNING           = E_WARNING;
    const E_PARSE             = E_PARSE;
    const E_NOTICE            = E_NOTICE;
    const E_STRICT            = E_STRICT;
    const E_DEPRECATED        = E_DEPRECATED;
    const E_CORE_ERROR        = E_CORE_ERROR;
    const E_CORE_WARNING      = E_CORE_WARNING;
    const E_COMPILE_ERROR     = E_COMPILE_ERROR;
    const E_COMPILE_WARNING   = E_COMPILE_WARNING;
    const E_USER_ERROR        = E_USER_ERROR;
    const E_USER_WARNING      = E_USER_WARNING;
    const E_USER_NOTICE       = E_USER_NOTICE;
    const E_USER_DEPRECATED   = E_USER_DEPRECATED;
    const E_ALL               = E_ALL;

    /* --------------------------------------------------------------------------------*
     |                              Class Properties                                   |
     |---------------------------------------------------------------------------------|
     */

    /**
     * Error handler Object
     * @var object
     */
    protected $x_error_handler;

    /**
     * Propertiy to handle and show if Current error is exception
     * @var boolean
     */
    private $x_isexception;

    /**
     * Error Type In string
     * @var array
     */
    protected $x_err_type_str = array(
        self::E_ERROR             => 'E_ERROR',
        self::E_RECOVERABLE_ERROR => 'E_RECOVERABLE_ERROR',
        self::E_WARNING           => 'E_WARNING',
        self::E_PARSE             => 'E_PARSE',
        self::E_NOTICE            => 'E_NOTICE',
        self::E_STRICT            => 'E_STRICT',
        self::E_DEPRECATED        => 'E_DEPRECATED',
        self::E_CORE_ERROR        => 'E_CORE_ERROR',
        self::E_CORE_WARNING      => 'E_CORE_WARNING',
        self::E_COMPILE_ERROR     => 'E_COMPILE_ERROR',
        self::E_COMPILE_WARNING   => 'E_COMPILE_WARNING',
        self::E_USER_ERROR        => 'E_USER_ERROR',
        self::E_USER_WARNING      => 'E_USER_WARNING',
        self::E_USER_NOTICE       => 'E_USER_NOTICE',
        self::E_USER_DEPRECATED   => 'E_USER_DEPRECATED',
        self::E_ALL               => 'E_ALL',
    );

    /* --------------------------------------------------------------------------------*
     |  Static properties to set Error HTML information                                |
     |---------------------------------------------------------------------------------|
     */
    
    /**
     * Replace for Error File
     * @var string
     */
    public static $x_html_error_type    = 'Error Type';
    

    /**
     * Replace for Error Message
     * @var string
     */
    public static $x_html_error_message = 'Error Message';
    
    /**
     * Replace for Error File
     * @var string
     */
    public static $x_html_error_file    = 'Error File';
    
    /**
     * Replace for Error Line
     * @var string
     */
    public static $x_html_error_line    = 'Error Line';

    /**
     * Replace for Error More
     * @var string
     */
    public static $x_html_error_more    = 'And %[more_error]% error.';

    /**
     * Replace for Document Root
     * @var string
     */
    public static $x_html_error_document_root = '{DOCUMENT ROOT}';

    /* --------------------------------------------------------------------------------*
     |                                Class Method                                     |
     |---------------------------------------------------------------------------------|
     */

    /**
     * PHP 5 Constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->x_error_handler = function ($arr, $exception = false) {
            // if has fatal error
            if (!empty($arr['is_fatal'])) {
                if (is_callable(Route::getDefault500())) {
                    Route::setDefault500(array("\\".__NAMESPACE__."\\Controller", 'error500'));
                }
                return call_user_func_array(
                    Route::getDefault500(),
                    array(
                        $arr
                    )
                );
            }
        };

        $this->x_isexception = false;
        /**
         * Register
         */
        set_error_handler(array($this, 'handleError'));
        set_exception_handler(array($this, 'handleException'));
        register_shutdown_function(array($this, 'handleShutdown'));
    }

    /**
     * Handle Shutdown Error
     */
    final public static function handleShutdown()
    {
        $last_error = error_get_last();
        if (isset($last_error) &&
            ($last_error['type'] & (E_ERROR | E_PARSE | E_CORE_ERROR | E_CORE_WARNING | E_COMPILE_ERROR | E_COMPILE_WARNING))
        ) {
            static::handleError(
                $last_error['type'],
                $last_error['message'],
                $last_error['file'],
                $last_error['line']
            );
        }
    }

    /**
     * Exception Handler
     * @param  object $exception object instance of exception handler
     */
    final public static function handleException($exception)
    {
        static::singleton()->x_isexception = true;
        static::handleError(
            $exception->getCode(),
            $exception->getMessage(),
            $exception->getFile(),
            $exception->getLine(),
            $exception
        );
    }

    /**
     * Alias Hanlde Error
     *
     * @param  integer  $severity  Error Code
     * @param  string   $message   Error Message
     * @param  string   $filepath  Error File Path
     * @param  integer  $line      Error Line
     * @param  object   $exception object instance of \Exception
     */
    final public static function set(
        $severity,
        $message,
        $filepath,
        $line,
        $exception = false
    ) {
        static::handleError($severity, $message, $filepath, $line, $exception);
    }

    /**
     * Handling Error
     *
     * @param  integer  $severity  Error Code
     * @param  string   $message   Error Message
     * @param  string   $filepath  Error File Path
     * @param  integer  $line      Error Line
     * @param  object   $exception object instance of \Exception
     */
    final public static function handleError(
        $severity,
        $message,
        $filepath,
        $line,
        $exception = false
    ) {
        $instance = static::singleton();
        $is_error = (((E_ERROR | E_COMPILE_ERROR | E_CORE_ERROR | E_USER_ERROR) & $severity) === $severity);
        // add error record
        $error = array(
            'type'      => $severity,
            'message'   => $message,
            'file'      => $filepath,
            'line'      => $line,
            'is_fatal'  => ($is_error ? true : false),
            'type_string'=> (isset($instance->x_err_type_str[$severity]) ? $instance->x_err_type_str[$severity] : 'UNKNOWN'),
            'error_exception' => ($instance->x_isexception ? $exception : null),
        );
        if ($is_error) {
            // Re`Set exception
            $instance->x_isexception = true;
            // doing clean buffers
            do {
                ob_clean();
                ob_end_clean();
            } while (ob_get_level());
        } elseif (0 == error_reporting()
            // if error reporting set to 0 and it has notice / warning
            && in_array($severity, array(E_USER_NOTICE, E_USER_WARNING, E_NOTICE, E_WARNING))
        ) {
            /**
             * write no error
             * that means still record error NOTICE and if set error_reporting(0)
             */
            Log::addNoError($error);
            $instance->x_isexception = false; // reset it again about exceptions
            return;
        }

        /**
         * Write Log
         */
        Log::addError($error);

        // call instance error handle output
        call_user_func_array(
            $instance->x_error_handler,
            array($error, $instance->x_isexception)
        );
        // if error
        if ($is_error || $instance->x_isexception) {
            exit(1); // and then exit here
        }

        $instance->x_isexception = false; // reset it again abotu exceptions
        unset($error);
    }

    /**
     * Get error Logs
     *
     * @return array
     */
    public static function getError()
    {
        return Log::getError();
    }

    /**
     * Get error Logs
     *
     * @return array
     */
    public static function getErrorNoRecord()
    {
        return Log::getError();
    }

    /**
     * HTML Error If exists
     */
    public static function htmlError()
    {
        /* ========================
         * Getting & Set config
         * ========================
         */
        $error_to_show_ = Config::get('show_error_count', 3);
        if (!is_numeric($error_to_show_) && ! empty($error_to_show_)) {
            $error_to_show = 3;
            Config::replace('show_error_count', ($error_to_show));
        } else {
            $error_to_show = abs($error_to_show_) < 0 || abs($error_to_show_) >= 30
                ? 30
                : (abs(intval($error_to_show_)));
            $error_to_show_ !== $error_to_show
                && Config::replace('show_error_count', $error_to_show);
        }
 
        /* ========================
         * Getting & Set Language
         * ========================
         */
        if (!is_string(static::$x_html_error_type) || !trim(static::$x_html_error_type)) {
            static::$x_html_error_type = 'Error Type';
        }
        if (!is_string(static::$x_html_error_message) || !trim(static::$x_html_error_message)) {
            static::$x_html_error_message = 'Error Message';
        }
        if (!is_string(static::$x_html_error_file) || !trim(static::$x_html_error_file)) {
            static::$x_html_error_file = 'Error File';
        }
        if (!is_string(static::$x_html_error_line) || !trim(static::$x_html_error_line)) {
            static::$x_html_error_line = 'Error Line';
        }
        if (! is_string(static::$x_html_error_more)
            || is_numeric(static::$x_html_error_more)
            || !trim(static::$x_html_error_more)
        ) {
            static::$x_html_error_more = null;
        } else {
            static::$x_html_error_more = 'And %[more_error]% more.';
        }
        if (! is_string(static::$x_html_error_document_root)
            || is_numeric(static::$x_html_error_document_root)
        ) {
            static::$x_html_error_document_root = null;
        } else {
            static::$x_html_error_document_root = !trim(static::$x_html_error_document_root)
            ? ''
            : '{DOCUMENT ROOT}';
        }

        $err_type = static::$x_html_error_type;
        $err_msg  = static::$x_html_error_message;
        $err_file = static::$x_html_error_file;
        $err_line = static::$x_html_error_line;
        $another_error = static::$x_html_error_more;
        $doc_root = static::$x_html_error_document_root;

        // default returns
        $html  = false;
        // get Error
        $error = static::getError();
        if ($error_to_show && !empty($error)) {
            $html  = "  <div class=\"x_error_info\">\n";
            $c     = 0;
            // length of document root
            $strlen_doc_root = strlen(Path::documentRoot());
            // split error to shown on html
            foreach ($error as $key => $value) {
                /**
                 * If static::$x_html_error_document_root is not null
                 * will be set alternative
                 */
                if (static::$x_html_error_document_root !== null) {
                    // safe output show replaced document root to static::$x_html_error_document_root
                    // default set {DOCUMENT ROOT}
                    $value['file'] = substr_replace(
                        $value['file'],
                        (
                            static::$x_html_error_document_root !== ''
                            ? '<span class="x_error_doc_root">'.static::$x_html_error_document_root.'</span>'
                            : ''
                        ),
                        0,
                        $strlen_doc_root
                    );
                }

                $html .= "    <div class=\"x_error_section\">\n";
                $html .= "      <table class=\"x_error_table\">\n";
                $html .= "        <tr class=\"x_error_type\">\n";
                $html .= "          <td class=\"x_error_label\"><span>{$err_type}</span></td>\n"
                        . "          <td class=\"x_error_value\"><span><span class=\"x_error_type_code\">{$value['type']}</span>"
                        . "<span class=\"x_error_type_string\">{$value['type_string']}</span>"
                        . "</span></td>\n";
                $html .= "        </tr>\n";
                $html .= "        <tr class=\"x_error_message\">\n";
                $html .= "          <td class=\"x_error_label\"><span>{$err_msg}</span></td>\n"
                        . "          <td class=\"x_error_value\"><span>{$value['message']}</span></td>\n";
                $html .= "        </tr>\n";
                $html .= "        <tr class=\"x_error_file\">\n";
                $html .= "          <td class=\"x_error_label\"><span>{$err_file}</span></td>\n"
                        . "          <td class=\"x_error_value\"><span>{$value['file']}</span></td>\n";
                $html .= "        </tr>\n";
                $html .= "        <tr class=\"x_error_line\">\n";
                $html .= "          <td class=\"x_error_label\"><span>{$err_line}</span></td>\n"
                        . "          <td class=\"x_error_value\"><span>{$value['line']}</span></td>\n";
                $html .= "        </tr>\n";
                $html .= "      </table>\n";
                $html .= "    </div>\n";
                $c++;
                /**
                 * check if has limit
                 */
                if ($c >= ($error_to_show) && ($error_count = count($error)-($c)) > 0) {
                    if (static::$x_html_error_more) {
                        $html .= "    <div class=\"x_error_more\">\n";
                        $html .= "      <div class=\"x_error_more_info\">"
                        . str_replace(
                            '%[more_error]%',
                            "<span class=\"x_error_more_count\">{$error_count}</span>",
                            static::$x_html_error_more
                        )
                        . "</div>\n";
                        $html .= "    </div>\n";
                    }

                    // stop
                    break;
                }
            }
            $html .= "  </div>";
            unset($error);
        }

        return $html;
    }
}
