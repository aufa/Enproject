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
use Aufa\Enproject\Helper\Filter;
use Aufa\Enproject\Helper\Html;
use Aufa\Enproject\Helper\Path;
use Aufa\Enproject\Helper\StringHelper;
use Aufa\Enproject\Helper\Template;
use Aufa\Enproject\Http\Request;
use Aufa\Enproject\Http\Response;
use Aufa\Enproject\ErrorHandler;
use ReflectionFunction;
use ReflectionMethod;

/**
 * Controller object class
 *     as Router controller to prevent change Method of current control
 */
class Controller extends Singleton
{
    /**
     * variable to tell the system has been fatal error
     * @var boolean
     */
    private static $x_is_fatal = false;

    /**
     * Set that request is head
     * @var null
     */
    private static $x_is_request_head = null;

    /* --------------------------------------------------------------------------------*
     |                          Route Context Handler                                  |
     |---------------------------------------------------------------------------------|
     */

    /**
     * Run the controller
     *
     * @return  void
     */
    final public static function run()
    {
        // being set cached here to prevent being overide
        static::$x_is_request_head = Request::isHead();

        /**
         * doing hook before routes if exists
         * in here hook execution could add route
         * or any what they are doing
         */
        Hook::doAction('x_before_route');

        // doing route
        Route::run();
        // set arguments default
        $args = array(
            'app'           => Enproject::singleton(),
            'response'      => Response::singleton(),
            'current_route' => Route::getCurrent(),
        );

        // get current route details
        $getCurentRoute  = Route::getCurrent();
        $Key       = $getCurentRoute['name'];
        $RouteKey  = $getCurentRoute['route'];
        // get method
        $method = Route::getMethod();
        // check if method is callable
        if (!is_callable($method)) {
            throw new \Exception("Route method is not exist!", E_USER_ERROR);
        }

        /**
         * Reflection check
         */
        if (is_array($method)) {
            $Reflection = new ReflectionMethod($method[0], $method[1]);
        } else {
            $Reflection = new ReflectionFunction($method);
        }

        /**
         * Split and fix method callback with array each of method for right way property uses
         */
        foreach ($Reflection->getParameters() as $key => $value) {
            $name = $value->getName();
            if (Route::getDefaultValue($Key, $RouteKey, $name, null) !== null) {
                $args[$name] = Route::getDefaultValue($Key, $RouteKey, $name);
                continue;
            }
            $pos = $value->getPosition();
            if (Route::getDefaultValue($Key, $RouteKey, $pos, null) !== null) {
                $args[$name] = Route::getDefaultValue($Key, $RouteKey, $pos);
                continue;
            }
            if ($pos < 3) {
                continue;
            }
            $custName = array_key_exists($name, $args)
                ? $name.$key
                : $name;
            if ($value->canBePassedByValue()) {
                if ($value->isOptional() && $value->isDefaultValueAvailable()) {
                    $args[$custName] = $value->isDefaultValueConstant()
                        ? constant($value->getDefaultValueConstantName())
                        : $value->getDefaultValue();
                } elseif ($value->allowsNull()) {
                    $args[$custName] = null;
                } elseif ($value->isArray()) { /* if array arguments */
                    $args[$custName] = array();
                } elseif ($value->isCallable()) { /* if callable arguments */
                    $args[$custName] = function () {
                    }; // closure is callable
                } else {
                    throw new \Exception(
                        sprintf(
                            "Parameter \$%s on Route must be can passed by value!",
                            $value->getName()
                        ),
                        E_USER_ERROR
                    );
                }
            } else {
                throw new \Exception(
                    sprintf(
                        "Parameter \$%s on Route must be can passed by value!",
                        $value->getName()
                    ),
                    E_USER_ERROR
                );
            }
        }

        // determine args as references
        $args = &$args;
        Benchmark::set('app', 'end');

        /**
         * intitalize template if possible
         * This must be called before calling Method
         */
        Template::init();

        // start buffer if not exists
        ob_get_level() || ob_start();

        // call the method
        call_user_func_array($method, $args);

        // call hook after routes
        Hook::doAction('x_after_route');
        // freed the memory
        unset($args, $Reflection, $name, $method);

        /**
         * Dont show display if Fatal Error
         * because fatal has call static::displayRender() it self
         */
        if (! static::$x_is_fatal && ! Route::isFatalError()) {
            // init display
            static::displayRender();
        }
    }

    /**
     * Agregate Display
     *
     * @return void
     */
    final private static function displayRender()
    {
        // set 500 fatal error
        if (static::$x_is_fatal || Route::isFatalError()) {
            static::$x_is_fatal = true; // set again
            Route::setFatalError(); // set fatal error
            Response::setStatus(500); // set 500
        } elseif (Route::isNoMatch()) {
            Response::setStatus(404); // set 404
        }

        /**
         * check again if not set on boolean
         */
        if (static::$x_is_request_head === null) {
            static::$x_is_request_head = Request::isHead();
        }

        /**
         * Get Request
         * This as cached variable to prevent Being Overide
         */
        $is_head_request = static::$x_is_request_head;

        /**
         * If not in Head request
         * get body content before and prepend it
         */
        if (!$is_head_request) {
            // start buffer if not exists
            ob_get_level() || ob_start();
            $body = ob_get_clean();
            /**
             * Prepend The Body if there's some output before prepend it
             */
            Response::prepend($body);
        } else {
            // if on head request set into empty string
            Response::setBody('');
        }

        /**
         * Fetch status, header, and body
         */
        list($status, $headers, $body) = Response::finalize();

        /**
         * Serialize cookies (with optional encryption)
         * set cookie header into Response
         */
        Response::serializeCookies($headers);

        /**
         * no headers hooks for fatal error
         */
        if (!static::$x_is_fatal && ! $is_head_request) {
            /**
             * Set OLD Header And status
             * for safe header request
             */
            $old_headers = $headers->all();
            $old_status  = $headers->all();

            /**
             * Doing Headers Hook
             * @var string
             */
            $headers = Hook::apply('x_headers', $headers->all());
            // if on hooks change headers has not array
            if (!is_array($headers)) {
                $headers = $old_headers;
            }

            /**
             * Doing Status Hook
             * @var string
             */
            $status  = (int) Hook::apply('x_header_status', $status);
            // if on hooks change status  and that is invalid
            if (!Response::getMessageForCode($status)) {
                $status = $old_status;
            }
            // freed
            unset($old_headers, $old_status);
        }

        /**
         * for safe method, check if headers
         * has not already sent.
         * header will be send into client
         * that if header has been sent , the header set will be
         * thrown an error
         */
        if (! headers_sent()) {
            /**
             * Send status header
             */
            if (strpos(PHP_SAPI, 'cgi') === 0) {
                header(sprintf('Status: %s', Response::getMessageForCode($status)));
            } else {
                header(
                    sprintf(
                        'HTTP/%s %s',
                        Config::get('http_version', '1.1'),
                        Response::getMessageForCode($status)
                    )
                );
            }

            /**
             * Send headers, getting all headers and set it
             */
            foreach ($headers as $name => $value) {
                if (!is_string($value)) {
                    continue;
                }
                $hValues = explode("\n", $value);
                foreach ($hValues as $hVal) {
                    header("{$name}: {$hVal}", false);
                }
            }
        }

        /**
         * Hook Body / Output Content
         * @var string
         */
        $body    = Hook::apply('x_before_output', $body);

        /**
         * Send body, but only if it isn't a HEAD request
         */
        if (!Request::isHead()) {

            /**
             * Hoks only available if no fatal
             */
            if (!static::$x_is_fatal) {
                /**
                 * Force tag Output
                 */
                if (Config::get('force_tag', false)) {
                    // force balance the tags
                    $body = Hook::apply(
                        'x_force_tag_output',
                        Filter::forceBalanceTags($body),
                        $body
                    );
                }
                /**
                 * Safe Output Check
                 */
                if (Config::get('safe_output', false)) {
                    // Filtering multibyte entities and set entities into false
                    $body = Hook::apply(
                        'x_safe_output',
                        Filter::multibyteEntities($body, false),
                        $body,
                        false
                    );
                }
                /**
                 * Inject Error Info if on debug mode
                 */
                if (Config::get('debug', false)) {
                    $error = ErrorHandler::HtmlError();
                    /**
                     * Insert Into Body content if exists
                     * if exist data-target='x_data_error' -> will be inserted here
                     * or will  be inserted into after open <body(.?)> tag
                     */
                    if ($error && is_string($error)) {
                        $body = Hook::apply(
                            'x_error_output',
                            (// check if has match data-target='x_data_error'
                                preg_match(
                                    '/(<div\s*(?:data\-target\=(\'|\")([\w:]*\s+)?x_data_error(\s+|$2)*)(?:[^>]*)>)/',
                                    $body
                                ) ?
                                // doing replace
                                    preg_replace(
                                        '/(<div\s*(?:data\-target\=(\'|\")([\w:]*\s+)?x_data_error(\s+|$2)*)(?:[^>]*)>(.*))/',
                                        "$1{$error}$2",
                                        $body
                                    )
                                : (// check if has tag body
                                    stripos($body, '<body') !== false && preg_match('/(<body\s*(?:[^>]*)>)/i', $body)
                                    ? preg_replace('/(<body\s*(?:[^>]*)>)/i', "$1\n{$error}", $body)
                                    : preg_replace("/^\s\s(\s*)/m", "$1", $error)."\n{$body}"
                                )
                            ),
                            $body
                        );
                    }
                }
            }

            /**
             * set again end of application
             */
            Benchmark::set('app', 'end');

            /**
             * check if contains shortcode here about %[
             * if exists will bereturning replace
             */
            if (strpos($body, "%[") !== false) {
                $body = str_replace(
                    array(
                        '%[benchmark]%',
                        '%[memory]%',
                        '%[real_memory]%',
                        '%[\benchmark\]%',   # This if has a escape short code
                        '%[\memory\]%',      # This if has a escape short code
                        '%[\real_memory\]%', # This if has a escape short code
                    ),
                    array(
                        round(Benchmark::get('app'), 6),
                        StringHelper::sizeFormat(Benchmark::getMemory(), 2),
                        StringHelper::sizeFormat(Benchmark::getRealMemory(), 2),
                        '%[benchmark]%',
                        '%[memory]%',
                        '%[real_memory]%',
                    ),
                    $body
                );

                /**
                 * fix escaped
                 * Above will be replace if only one
                 */
                strpos($body, "%[") !== false && $body = preg_replace(
                    '/(\%\[)\\\(\\\+)(benchmark|memory|real\_memory)\\\(\\\+)(\]\%)/',
                    '$1$2$3$4$5',
                    $body
                );
            }

            /**
             * Clean Body Output from empty non ascii characters
             * set second parameters to false because this is not URL
             */
            $body = StringHelper::removeInvisibleCharacters($body, false);

            /**
             * set response body
             */
            Response::setBody(Hook::apply('x_before_output', $body));
            if (!headers_sent() && in_array('Content-Length', headers_list())) {
                header('Content-Length: '. Response::getLength(), true);
            }

            /**
             * freed memory
             */
            unset($body, $headers);

            /**
             * starting buffer if buffer has been cleaned before
             */
            ob_get_level() || ob_start();

            /**
             * print output
             */
            echo Response::getBody();

            /**
             * Set Hook after output if not in Fatal
             */
            if (!static::$x_is_fatal) {
                // do after
                Hook::doAction('x_after_output', Response::getBody());

                /**
                 * start buffer again if bugger is cleared
                 */
                ob_get_level() || ob_start();

                /**
                 * doing after all end of system
                 */
                Hook::doAction('x_after_all');
            }

            /**
             * set response body to empty freed the memory
             * Reset Body
             */
            Response::setBody('');
        }

        // restore error handler -> end
        restore_error_handler();
    }

    /* --------------------------------------------------------------------------------*
     |                          Ouput View Default Handler                             |
     |---------------------------------------------------------------------------------|
     */

    /**
     * Default View Handler
     */
    public static function defaultView()
    {
        $template  = Template::singleton();
        $directory = $template::getTemplateDirectory();
        if ($directory) {
            $template_dir = $template->getActiveTemplateDirectory();
            $file_default = $template->x_default_file;
            if ($template_dir && $file_default && is_string($file_default)) {
                if (is_file("{$template_dir}/{$file_default}")) {
                    // using callback to prevent direct access
                    return call_user_func(
                        function ($a) {
                            require $a;
                        },
                        "{$template_dir}/{$file_default}"
                    );
                }
            }
        }
        $addition = '';
        if (!$directory) {
            $addition = "\n<div class=\"info\">\n"
                . "<p>Looks Like your <strong>(templates)</strong> directory does not exists, "
                . " you could put your template structures as :<br />"
                . "<code><strong>{WEB ROOT}</strong>/templates/yourtemplatename/default.php<em>[and list of all your file]</em></code>"
                . "</p>"
                . "</div>";
        }
        // returning default
        Response::setBody(
            Html::create(
                'Wellcome',
                "<h1 class=\"big\">Wellcome</h1>\n"
                    ."<h2 class=\"desc\">To Our Home</h2>\n"
                    ."<p>Default Home Page Site</p>\n"
                    ."<p><small>Render in: %[benchmark]% second, memory: %[memory]%</small></p>{$addition}",
                array(
                    'style' => "body{font-size: 14px;font-family: helvetica, arial, sans-serif;color: #555;line-height: normal;background: #f1f1f1;}\n"
                        . ".wrap{margin: 0 auto;max-width: 700px;text-align: center;}\n"
                        . ".big{font-size:80px;margin: 2em 0 20px;}.desc{font-size: 28px;margin: .3em 0 0;}\n"
                        . ".info{font-size:13px;margin: 1em 0; padding: 5px 10px;text-align: left;background: #fff;color: #555;border:1px solid #ddd;}\n"
                        . "code {padding: 5px 7px;border: 1px solid #eee;background: #f9f9f9;margin: 1em 0;display: block;font-family:monospace;}\n"
                        . "@media(max-width: 480px){.big{font-size: 60px;}.desc{font-size: 24px;}}"
                )
            )
        );
    }

    /**
     * Default Not found output Handler
     */
    public static function defaultNotFound()
    {
        // set Not Found route
        Route::setNotfound();
        $template =  Template::singleton();
        $template_dir = $template->getActiveTemplateDirectory();
        $file_404    = $template->x_404_file;
        if ($template_dir && $file_404 && is_string($file_404)) {
            if (is_file("{$template_dir}/{$file_404}")) {
                // using callback to prevent direct access
                return call_user_func(
                    function ($a) {
                        ob_start();
                        require $a;
                        $content = ob_get_clean();
                        Response::write($content, true);
                    },
                    "{$template_dir}/{$file_404}"
                );
            }
        }

        // empty
    }

    /**
     * Default Blocked URL output Handler
     */
    public static function errorBlocked()
    {
        // set 400 Bad Request
        Response::setStatus(400);
        $template =  Template::singleton();
        $template_dir = $template->getActiveTemplateDirectory();
        $file_blocked    = $template->x_blocked_file;
        if ($template_dir && $file_blocked && is_string($file_blocked)) {
            if (is_file("{$template_dir}/{$file_blocked}")) {
                // using callback to prevent direct access
                return call_user_func(
                    function ($a) {
                        ob_start();
                        require $a;
                        $content = ob_get_clean();
                        Response::setBody($content);
                    },
                    "{$template_dir}/{$file_blocked}"
                );
            }
        }
        Response::setBody(
            Html::create(
                'Bad Gateway',
                "<h1 class=\"big\">400</h1>\n"
                    ."<h2 class=\"desc\">Bad Gateway</h2>\n"
                    ."<p>Please try another URL</p>",
                array(
                    'style' => "body{font-size: 14px;font-family: helvetica, arial, sans-serif;color: #555;line-height: normal;background: #f1f1f1;}\n"
                        . ".wrap{margin: 0 auto;max-width: 700px;text-align: center;}\n"
                        . ".big{font-size: 180px;margin: .7em 0 20px;}.desc{font-size: 28px;margin: .3em 0 0;}"
                )
            )
        );

        // doing display
        static::displayRender();
    }

    /**
     * Default error 500 output Handler
     */
    public static function error500()
    {
        $args_        = func_get_args();
        $template     = Template::singleton();
        $template_dir = $template->getActiveTemplateDirectory();
        static::$x_is_fatal = true;
        if ($template_dir && $template->x_500_file && is_string($template->x_500_file)) {
            if (is_file("{$template_dir}/{$template->x_500_file}")) {
                $message = (array) reset($args_);
                // using callback to prevent direct access
                return call_user_func(
                    function ($a) use ($message) {
                        ob_start();
                        require $a;
                        $content = ob_get_clean();
                        Response::setBody($content);
                        static::displayRender();
                        exit(1); // and then exit here
                    },
                    "{$template_dir}/{$template->x_500_file}"
                );
            }
        }

        /**
         * Body container
         * @var string
         */
        $body = "<h1 class=\"big\">500</h1>\n";
        if (Config::get('debug', true)) {
            $args_  = current($args_);
            $strlen_doc_root = strlen(Path::documentRoot());
            // safe output show replaced document root to {DOCUMENT ROOT}
            $args_['file'] = substr_replace(
                $args_['file'],
                '<span class="x_error_doc_root">{DOCUMENT ROOT}</span>',
                0,
                $strlen_doc_root
            );
            $body  .= "    <div class=\"x_error_section\">\n"
                . "      <table class=\"x_error_table\">\n"
                . "        <tr class=\"x_error_type\">\n"
                . "          <td class=\"x_error_label\"><span>Error Type</span></td>\n"
                . "          <td class=\"x_error_value\"><span><span class=\"x_error_type_code\">{$args_['type']}</span>"
                . "<span class=\"x_error_type_string\">{$args_['type_string']}</span></span></td>\n"
                . "        </tr>\n"
                . "        <tr class=\"x_error_message\">\n"
                . "          <td class=\"x_error_label\"><span>Error Message</span></td>\n"
                . "          <td class=\"x_error_value\"><span>{$args_['message']}</span></td>\n"
                . "        </tr>\n"
                . "        <tr class=\"x_error_file\">\n"
                . "          <td class=\"x_error_label\"><span>Error File</span></td>\n"
                . "          <td class=\"x_error_value\"><span>{$args_['file']}</span></td>\n"
                . "        </tr>\n"
                . "        <tr class=\"x_error_line\">\n"
                . "          <td class=\"x_error_label\"><span>Error Line</span></td>\n"
                . "          <td class=\"x_error_value\"><span>{$args_['line']}</span></td>\n"
                . "        </tr>\n"
                . "      </table>\n"
                . "    </div>\n";
        } else {
            $body .= "<h2 class=\"desc\">Internal Server Error</h2>\n"
                ."<p>We are sorry for inconvenience</p>";
        }
        /**
         * Set Body
         */
        Response::setBody(
            Html::create(
                'Internal Server Error',
                $body,
                array(
                    'style' => "body{font-size: 14px;font-family: helvetica, arial, sans-serif;color: #555;line-height: normal;background: #f1f1f1;}\n"
                        . ".wrap{margin: 0 auto;max-width: 700px;text-align: center;}\n"
                        . (
                            Config::get('debug', false)
                            ? ".x_error_section{display:block;padding: 10px;background: #fff;border: 1px solid #ddd;}\n"
                                . ".x_error_table{border-collapse: collapse;border:0;border-spacing:0;}\n"
                                . ".x_error_label{padding: 5px 10px;text-align: left;border-right: 2px solid #bbb;}\n"
                                . ".x_error_value{padding: 5px 10px;text-align: left;border-right: 0px solid #ddd;}\n"
                                . ".x_error_type .x_error_type_string{background: #f18181;padding: 3px 5px;color:#fff;font-weight: bold;margin-left:0px;}\n"
                                . ".x_error_type .x_error_type_code{background: #4359fe;margin-right: 0px;padding: 3px 6px;color:#fff;font-weight: bold;}\n"
                            : ''
                        )
                        . ".big{font-size: 180px;margin: .7em 0 20px;}\n.desc{font-size: 28px;margin: .3em 0 0;}"
                )
            )
        );

        // doing display
        static::displayRender();
        exit(1); // and then exit here
    }
}
