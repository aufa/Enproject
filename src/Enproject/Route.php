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
use Aufa\Enproject\Url;
use Aufa\Enproject\Helper\Internal;
use Aufa\Enproject\Http\Response;
use Aufa\Enproject\Request\Server;
use ReflectionFunction;
use ReflectionMethod;

/**
 * Route Handler Class, for handling Router & Route
 * That determine by User & System
 */
class Route extends Singleton
{
    /* --------------------------------------------------------------------------------*
     |                              Class Properties                                   |
     |---------------------------------------------------------------------------------|
     */

    /**
     * Routes Records
     * @var array
     */
    protected $x_routes = array(
        // the routes default use 2 routegroup
        'main' => array(),
        'default' => array()
    );

    /**
     * Default view Handler
     * @var callable
     */
    protected $x_default_view;

    /**
     * Default 404 not found handler
     * @var callable
     */
    protected $x_default_notfound;

    /**
     * Default error eror handler
     * @var callable
     */
    protected $x_default_500;

    /**
     * Default Blocked eror handler
     * @var callable
     */
    protected $x_default_blocked;

    /**
     * Current Route
     * @var [type]
     */
    protected $x_current_route = array(
        'name' => null,
        'route' => null
    );

    /**
     * If is not found Route
     * @var boolean
     */
    protected $x_is_no_match = false;

    /**
     * if System Is fatal error
     * @var boolean
     */
    protected $x_is_error_fatal = false;

    /**
     * Last Route Record
     * @var null
     */
    private $x_last_route = null;

    /**
     * Method If Route Match
     */
    private $x_method = null;

    /**
     * Default Route Name
     * @var string
     */
    private $x_default_name = 'default';

    /**
     * List Protected Route
     * @var array
     */
    private $x_protected_route = array();

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

        /**
         * Set Routes Default
         */
        $this->x_routes['main'] = array();
        $this->x_routes[$this->x_default_name] = array();

        /**
         * Set default Current Route
         * @var array
         */
        $this->x_current_route = array('name' => null, 'route' => null);
        $this->x_default_view  = array("\\".__NAMESPACE__.'\Controller', 'defaultView');
        $this->x_default_notfound = array("\\".__NAMESPACE__.'\Controller', 'defaultNotFound');
        $this->x_default_500 = array("\\".__NAMESPACE__.'\Controller', 'error500');
        $this->x_default_blocked = array("\\".__NAMESPACE__.'\Controller', 'errorBlocked');
    }

    /**
     * Add route into system
     *
     * @param string   $name         Route Group
     * @param string   $routeVal     route regex
     * @param callable $callable     the functions to call
     * @param mxed    $custom_method the method (ALL|POST|GET|PUT|DELETE|GET|HEAD)
     *                               use array or string ( string use | as separator)
     */
    public static function add($name, $routeVal, callable $callable, $custom_method = null)
    {
        $instance = static::singleton();
        if (is_callable($callable)) {
            $name = ! $name
                ? $instance->x_default_name
                : $name;
            if (!is_string($name)) {
                trigger_error("Invalid route name specified!", E_USER_ERROR);
                return $instance;
            }
            if (!is_string($routeVal)) {
                trigger_error(
                    "Invalid route specified, route must be a regex and as a string!",
                    E_USER_ERROR
                );
                return $instance;
            }
            // set again
            $name = trim($name)
                ? strtolower(trim($name))
                : $instance->x_default_name;
            if (static::isProtected($name, $routeVal)) {
                trigger_error(
                    "Route name <strong>{$name}</strong> with route <strong>{$routeVal}</strong> has protected!",
                    E_USER_NOTICE
                );
                return $instance;
            }
            // set route
            $instance->x_routes[$name][$routeVal] = array(
                'function' => $callable,
                'method' => $custom_method,
                'param' => array()
            );
            // set last route
            $instance->x_last_route = array(
                'name'  => $name,
                'route' => $routeVal,
            );
            ! $custom_method && $custom_method = 'ALL';
            $instance::method($custom_method);
        }
        return $instance;
    }

    /**
     * Set Custom Route Method
     *
     * @param  string $custom_method the custom method
     * @return object $instance      Current Class
     */
    public static function method($custom_method)
    {
        $instance = self::singleton();
        $last_route = empty($instance->x_last_route['route'])
            || ! isset($instance->x_last_route['name'])
            ? null
            : $instance->x_last_route;
        if (!$last_route
            || ! isset($instance->x_routes[$last_route['name']][$last_route['route']]['function'])
            || ! is_callable($instance->x_routes[$last_route['name']][$last_route['route']]['function'])
        ) {
            if (isset($last_route)) {
                unset($instance->x_routes[$last_route['name']][$last_route['route']]);
            }
            return $instance;
        }

        if (is_string($custom_method)) {
            $method = strtoupper(trim($custom_method));
            if (strpos($custom_method, '|') !== false) {
                $custom_method = explode('|', $method);
                $custom_method = array_map('trim', $custom_method);
            }
        }

        if (is_array($custom_method)) {
            $custom_method = Internal::strtoUpper($custom_method);
            if (in_array('ALL', $custom_method)) {
                $method = 'ALL';
            } else {
                $method = '';
                foreach ($custom_method as $value) {
                    if (is_string($value)) {
                        if ($value != 'ALL' || !defined("\\".__NAMESPACE__."\\Http\\Request::{$value}")) {
                            continue;
                        }
                        $value = trim(strtoupper($value));
                        $method .= "{$value}|";
                        if ($value == 'ALL') {
                            $method = 'ALL';
                            break;
                        }
                    }
                }
                $method = trim($method, "|");
            }
        }

        /**
         * Set & CHeck Method
         */
        if (!$method ||
            is_string($custom_method) && strpos($method, '|') === false
            && ($method == 'ALL' || ! defined("\\".__NAMESPACE__."\\Http\\Request::{$method}"))
        ) {
            $method = 'ALL';
        }

        $key   = $last_route['name'];
        $route = $last_route['route'];
        // reset
        $x_method = null;
        /**
         * Check Method parsing array
         */
        if (!empty($instance->x_routes[$key][$route]) && $method != 'ALL'
        ) {
            $x_method = strtoupper($instance->x_routes[$key][$route]['method']);
            $ex = $x_method == 'ALL'
                ? array()
                : explode('|', $x_method);
            $method = array_unique(array_merge($ex, explode('|', $method)));
            $method = implode('|', $method);
        }
        $instance->x_routes[$key][$route]['method'] = trim($method, '|');
        if (!isset($instance->x_routes[$key][$route]['param']) || !is_array($instance->x_routes[$key][$route]['param'])) {
            $instance->x_routes[$key][$route]['param'] = array();
        }
        unset($x_method, $method);
        // $instance->x_last_route = null;
        return $instance;
    }

    /**
     * Alias static::method()
     */
    public static function setMethod($custom_method)
    {
        return static::method($custom_method);
    }

    /* --------------------------------------------------------------------------------*
     |                               Route Set Method                                  |
     |---------------------------------------------------------------------------------|
     */

    public static function all()
    {
        static::singleton()->Method('ALL');
        return static::singleton();
    }

    public static function post()
    {
        static::singleton()->Method('POST');
        return static::singleton();
    }

    public static function put()
    {
        static::singleton()->Method('PUT');
        return static::singleton();
    }

    public static function get()
    {
        static::singleton()->Method('GET');
        return static::singleton();
    }

    public static function patch()
    {
        static::singleton()->Method('PATCH');
        return static::singleton();
    }

    public static function delete()
    {
        static::singleton()->Method('DELETE');
        return static::singleton();
    }

    public static function options()
    {
        static::singleton()->Method('OPTIONS');
        return static::singleton();
    }

    public static function cli()
    {
        static::singleton()->Method('CLI');
        return static::singleton();
    }

    public static function head()
    {
        static::singleton()->Method('HEAD');
        return static::singleton();
    }

    /* --------------------------------------------------------------------------------*
     |                          Route Progress & Parse                                 |
     |---------------------------------------------------------------------------------|
     */

    /**
     * Running route
     */
    public static function run()
    {
        static $called;
        if (!$called) {
            $next = debug_backtrace();
            $next = next($next);
            if (empty($next['class'])
                || $next['class'] != __NAMESPACE__.'\\Controller'
                || strtolower($next['function']) != 'run'
            ) {
                Enproject::Run(); // instance Call run
            }

            $called = true;
            // start Benchmark
            Benchmark::set('route', 'start');
            static::singleton()->parse();
        }
        return static::singleton();
    }

    /**
     * Parse x_Routes
     *
     * Matches any x_routes that may exist in the x_routes definition
     * @access private
     */
    protected function parse()
    {
        // remove not found
        self::removeNotFound();
        // Turn the segment array into a URI string
        $uri = implode('/', Url::allSegment());

        // Get HTTP verb
        $http_verb = strtoupper(trim(Server::get('REQUEST_METHOD', 'CLI')));
        if (! isset($this->x_routes['main'])) {
            $this->x_routes['main'] = array();
        }
        if (! isset($this->x_routes['default'])) {
            $this->x_routes['default'] = array();
        }
        $default_array = array(
            'main'   => $this->x_routes['main'],
            'default' => $this->x_routes['default'],
        );
        // sort it again
        $this->x_routes = array_merge($default_array, $this->x_routes);
        unset($default_array); // freed
        foreach ($this->x_routes as $routeKey => $route) {
            if (empty($route)) {
                continue;
            }
            // Is there a literal match?  If so we're done
            if (isset($route[$uri])
                // lets continue
                && ! preg_match('/[\)\(]/', $uri) // not on literal
                && strpos($uri, ':any') === false
                && strpos($uri, ':num') === false
                && is_callable($route[$uri]['function'])
                && $route[$uri]['method'] == $http_verb
            ) {
                $this->x_current_route = array('name' => $routeKey, 'route' => $uri);
                return $this->setRequest($route[$uri]['function']);
            }

            // Loop through the route array looking for wildcards
            foreach ($route as $key => $Rval) {
                if (!is_array($Rval)) {
                    continue;
                }

                // $keyTmp = $key;
                // Convert wildcards to RegEx
                $key = str_replace(array(':any', ':num'), array('[^/]+', '[0-9]+'), $key);
                if (strpos($key, '/') === 0) {
                    $key = "/".substr($key, 1);
                } elseif ($key && preg_match('/(^\(\?[iUXxms]\))/', $key)) {
                    // parentesis
                    $key = preg_replace_callback('/(^\(\?[iUXxms]\))(.*)/', function ($match) {
                        return $match[1].(strpos($match[2], '/') === 0 ? substr($match[2], 1) : $match[2]);
                    }, $key);
                } else {
                    $key = "/{$key}";
                }
                // fix :any or [^/]+ on first statements
                $key = preg_replace('/^(\/|\(\?[iUXxms]\))(\(+)?(\[\^\/\]\+)(\)+)/', "$1(.?$3)", $key);

                // Does the RegEx match?
                if (preg_match('#^'.$key.'$#', '/'.$uri, $matches)) {
                    // Are we using callbacks to process back-references?
                    if (is_callable($Rval['function'])) {
                        if (($Rval['method'] == 'ALL' || in_array($http_verb, explode('|', $Rval['method'])))) {
                            $this->x_current_route = array('name' => $routeKey, 'route' => $key);
                            return $this->setRequest($Rval['function']);
                        }
                    }
                }
            }
        }

        // set Not Found
        empty($uri) || self::setNotfound();
        // If we got this far it means we didn't encounter a
        // matching route so we'll set the site default route
        return $this->setRequest();
    }

    /**
     * Set request route
     *
     * Takes an array of URI segments as input and sets the callable method
     * to be called.
     *
     * @access private
     * @param   array   $segments   URI segments
     * @return  object
     */
    protected function setRequest($segments = array())
    {
        if (!static::isBlocked()) {
            $method = $this->x_default_view;
            if (!empty($segments)) {
                if (is_array($segments) && is_callable(reset($segments))) {
                    $method = reset($segments);
                } elseif (is_callable($segments)) {
                    $method = $segments;
                }
            }
            if ($this->x_is_no_match) {
                Response::setStatus(404);
                $method = $this->x_default_notfound;
            }
            if (!is_callable($method)) {
                Response::setStatus(500);
                $method = $this->x_default_500;
            }
        } else {
            // blocked is same as 404
            Response::setStatus(404);
            static::setNotfound();
            $method = $this->x_default_blocked;
        }
        $this->x_method = $method;
        // end benchmark
        Benchmark::set('route', 'end');
        return $this;
    }

    /* --------------------------------------------------------------------------------*
     |                       Route Set & Handler Context                               |
     |---------------------------------------------------------------------------------|
     */

    /**
     * Protect Route that being override
     *
     * @param  string $routeName route group
     * @param  string $routeVal  route regex
     * @return object            $this
     */
    public static function protect($routeName = null, $routeVal = null)
    {
        $instance = static::singleton();
        if ($routeName === null && $routeVal === null) {
            $routes = $instance->x_last_route;
        } else {
            $router = static::getRouteFor($routeName, $routeVal);
            if (!empty($router)) {
                $routes['name']  = trim(strtolower($routeName));
                $routes['route'] = $routeVal;
            }
            unset($router);
        }
        if (isset($routes['name'])) {
            if (isset($instance->x_protected_route[$routes['name']])
                && ! in_array($routeName, $instance->x_protected_route[$routes['name']])
                || empty($instance->x_protected_route[$routes['name']])
            ) {
                $instance->x_protected_route[$routes['name']][] = $routes['route'];
            }
        }

        return $instance;
    }

    /**
     * Set Default Value for Route Parameter
     *
     * @param  string|integer|array   $position  the arguments set value
     * @param  mixed                  $value     Default return value
     * @return object                 $instance  CUrrent Class
     */
    public static function defaultValue($position, $value = null)
    {
        $instance = static::singleton();
        if (empty($instance->x_last_route) || ! is_callable(reset($instance->x_last_route))) {
            return $instance;
        }
        $last_route = $instance->x_last_route;
        $key   = $last_route['name'];
        $route = $last_route['route'];
        if (!empty($instance->x_routes[$key][$route]['function'])) {
            if (is_array($instance->x_routes[$key][$route]['function'])) {
                $Reflection = new ReflectionMethod($instance->x_routes[$key][$route]['function'][0], $instance->x_routes[$key][$route]['function'][1]);
            } else {
                $Reflection = new ReflectionFunction($instance->x_routes[$key][$route]['function']);
            }
            if (count($Reflection->getParameters())) {
                if (is_array($position)) {
                    $instance->x_routes[$key][$route]['param'] = array_merge($instance->x_routes[$key][$route]['param'], $position);
                } elseif (is_numeric($position) || !is_string($position)) {
                    $instance->x_routes[$key][$route]['param'] = array_merge(
                        $instance->x_routes[$key][$route]['param'],
                        array($position => $value)
                    );
                }
            }
            unset($Reflection);
        }
        return $instance;
    }

    /**
     * Alias static::defaultValue()
     */
    public static function setDefaultValue($position, $value = null)
    {
        return static::defaultValue($position, $value);
    }

    public static function setNotfound()
    {
        static::singleton()->x_is_no_match = true;
    }

    public static function setFatalError()
    {
        static::singleton()->x_is_error_fatal = true;
    }

    public static function setDefaultView($callable)
    {
        if (is_callable($callable)) {
            static::singleton()->x_default_view = $callable;
        }
        return static::singleton();
    }

    public static function setDefaultNotFound($callable)
    {
        if (is_callable($callable)) {
            static::singleton()->x_default_notfound = $callable;
        }
        return static::singleton();
    }

    public static function setDefault500($callable)
    {
        if (is_callable($callable)) {
            static::singleton()->x_default_500 = $callable;
        }
        return static::singleton();
    }

    public static function removeNotFound()
    {
        static::singleton()->x_is_no_match = false;
    }

    public static function removeFatalError()
    {
        static::singleton()->x_is_error_fatal = false;
    }

    /* --------------------------------------------------------------------------------*
     |                              Getting Route Data                                 |
     |---------------------------------------------------------------------------------|
     */

    public static function getDefaultValue($routeName = null, $RouteVal = null, $position = null, $default = null)
    {
        if (is_null($routeName) || is_null($RouteVal)) {
            $routes    = static::getCurrent();
            $routeName = $routes['name'];
            $RouteVal  = is_null($RouteVal) ? $routes['route'] : $RouteVal;
        }
        if (static::exist($routeName, $RouteVal)) {
            $instance = static::singleton();
            if (is_null($position)) {
                return $instance->x_routes[$routeName][$RouteVal]['param'];
            } elseif (!is_object($position) && !is_array($position)
                && array_key_exists($position, $instance->x_routes[$routeName][$RouteVal]['param'])
            ) {
                return $instance->x_routes[$routeName][$RouteVal]['param'][$position];
            }
        }
        return $default;
    }

    public static function getDefaultView()
    {
        return static::singleton()->x_default_view;
    }

    public static function getDefaultNotFound()
    {
        return static::singleton()->x_default_notfound;
    }

    public static function getDefault500()
    {
        return static::singleton()->x_default_500;
    }

    public static function getRouteAdmin()
    {
        return static::singleton()->x_route_admin;
    }

    public static function getRouteCron()
    {
        return static::singleton()->x_route_cron;
    }

    public static function getRouteRSS()
    {
        return static::singleton()->x_route_rss;
    }
    public static function getRouteAPI()
    {
        return static::singleton()->x_route_api;
    }

    public static function getMethod()
    {
        return self::singleton()->x_method;
    }

    public static function getAllRoutes()
    {
        return (array) self::singleton()->x_routes;
    }

    public static function getCurrent()
    {
        return static::singleton()->x_current_route;
    }

    public static function getCurrentName()
    {
        return static::singleton()->x_current_route['name'];
    }

    public static function getCurrentRouteVal()
    {
        return static::singleton()->x_current_route['route'];
    }

    public static function getRouteGroupFor($routeName)
    {
        if (!is_object($routeName) && !is_array($routeName)) {
            $allRoute = static::getAllRoutes();
            // sanitize it with real route group
            $routeName = strtolower(trim($routeName));
            return array_key_exists($routeName, $allRoute)
                ? $allRoute[$routeName]
                : null;
        }
        return null;
    }

    public static function getRouteFor($routeName, $route)
    {
        if (!is_object($routeName) && !is_array($routeName) && $route) {
            $routes = static::getRouteGroupFor($routeName);
            if (!empty($routes) && is_array($routes)) {
                return array_key_exists($route, $routes)
                    ? $routes[$route]
                    : null;
            }
        }
        return null;
    }

    /* --------------------------------------------------------------------------------*
     |                               Route Checking                                    |
     |---------------------------------------------------------------------------------|
     */

    /**
     * Check if Roue exists
     *
     * @param  string $routeName route group
     * @param  string $route     route regex
     * @return boolen            true if exist
     */
    public static function exist($routeName, $route = null)
    {
        if (!is_object($routeName) && !is_array($routeName)) {
            $allRoute = static::getAllRoutes();
            // sanitize it with real route group
            $routeName = strtolower(trim($routeName));
            if (is_null($route)) {
                return array_key_exists($routeName, $allRoute);
            }
            return !empty($allRoute[$routeName][$route]);
        }
        return false;
    }

    /**
     * Aliases static::exist()
     *
     * @param  string $routeName route group
     * @param  string $route     route regex
     * @return boolen            true if exist
     */
    public static function has($routeName, $route = null)
    {
        return static::exist($routeName, $route = null);
    }

    /**
     * Aliases static::exist()
     *
     * @param  string $routeName route group
     * @param  string $route     route regex
     * @return boolen            true if exist
     */
    public static function exists($routeName, $route = null)
    {
        return static::exist($routeName, $route = null);
    }

    /**
     * Check if route Has been protected
     *
     * @param  string  $routeName route group
     * @param  string  $routeVal  route regex
     * @return boolean            true if protected
     */
    public static function isProtected($routeName, $routeVal)
    {
        $instance  = static::singleton();
        $router    = $instance::getRouteFor($routeName, $routeVal);
        $routeName = $router ? strtolower(trim($routeName)) : null;
        if ($routeName !== null && isset($instance->x_protected_route[$routeName])) {
            return in_array($routeVal, $instance->x_protected_route[$routeName]);
        }
        return false;
    }

    /**
     * Check if current request is Blocked or not allowed by route
     *
     * @return boolean true if blocked
     */
    public static function isBlocked()
    {
        return Url::isBlocked();
    }

    /**
     * Check if current request is No match or Not found match route
     *
     * @return boolean true if no match / 404 not found route
     */
    public static function isNomatch()
    {
        return static::singleton()->x_is_no_match;
    }

    /**
     * Check if current request is has fatal error
     *
     * @return boolean true if has fatal error
     */
    public static function isFatalError()
    {
        return static::singleton()->x_is_error_fatal;
    }

    /**
     * Destruct on end of class proccess -> __destruct for backward Compatibility
     */
    public function __destruct()
    {
    }
}
