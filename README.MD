# Enproject

[BETA DEVELOPMENT] Enproject Simple PHP Framework Library

Create Application using `Enproject` Simple Framework easily.

Base on MWYS (Models Whatever You Say)

For some class like `Hook` use WordPress Hook methods, also some methode use another  library
and inspire by CMS / PHP Framework. Use built in template refference helper that by default templates directory based on:

`{root_directory}/templates`

Scan container of sub templates directory.

```php

/**
 * Run Instance Easily Create Default View
 * will be render templates/Default/default.php
 * or on template directory
 */
\Aufa\Enproject\Enproject::Run();
```

# Tribute

* [WordPress](https://wordpress.org)

* [Code Igniter](https://codeigniter.com)

* [Slim framework](http://www.slimframework.com)

* [Klein PHP](http://chriso.github.io/klein.php/)

* [PhPass](http://www.openwall.com/phpass/)


## Add Route
Adding Route just like Code Igniter uses, but it will be more efficient called and you could determine
default return value by position also determine you have set method.

```php

/**
 * Set Route To any
 * - main as position of route, `main` means Route Group that will be execute as main priority
 *      if you set and empty value will be use as default route and execute second.
 * - '/' mean the regex as accessing URL Route access as '/' or empty url
 *      The regex like CI(Code Igniter use) :any as [^/]+ and :num as [0-9]+
 * by default returning  of callback use 3 arguments
 *  1. \Aufa\Enproject\Enproject::Singleton() // as main application
 *  2. \Aufa\Enproject\Response::Singleton() // as response application
 *  3. \Aufa\Enproject\Route::getCurrent() // as current route
 *
 * @return object \Aufa\Enproject\Route Singleton
 */
\Aufa\Enproject\Route('main', '/', function ($a, $b, $c) {
    // doing some thing here   
})
// as request by any Request method
->all()
// set default value
->defaultValue(
    'a' => null, // @param $a as null default
    'b' => true, // @param $b as boolean true default
    'c' => new stdClass() // @param $c as object stdClass
)
// protecting route being overide
->protect();

// and more ...

```
## Config Default
Configuration of Enproject use :

```php

/**
 * Set Config
 */
\Aufa\Enproject\Config::set('config name', ([mixed] value));

/**
 * If want to protect last config just set
 * protecting config prevent configuration being overide
 */
\Aufa\Enproject\Config::protect();

// or use
\Aufa\Enproject\Config::set('config name', ([mixed] value))->protect();
/**
 * Or protecting configs using value
 */
\Aufa\Enproject\Config::protect('keyname_config');

// or use array as keyname multiple protect
\Aufa\Enproject\Config::protect(['keyname_config', 'keyname_config_1', 'keyname_config_2']);
```

* `uri_sufix` (string) default empty string

* `templates_directory` (string) default `templates` take from root directory of web

* `debug` (boolean) false true if rendering debug even it notices

* `security_salt`   (string) random string for encryption & cookie encryption default empty string

* `security_key`    (string) random string for encryption cookie default empty string

* `session_hash`    (string) random string for encryption session cookie default empty string

* `cookie_lifetime` (integer) Cookie lifetime default `null`

* `cookie_domain`   (string) Domain to be as cookie domain default `null`

* `cookie_secure`   (boolean) true if only allow secure connection default `false`

* `cookie_httponly` (boolean) true if use http only default `false`

* `safe_output`     (boolean) true if use entities of multibytes string (convert non ASCII to entities) default `false`

* `force_tag`       (boolean) true if use force balanced tags of html output default `false`

* `http_version`    (string)  http version default `1.1`

* `show_error_count` (integer) maximum error to show if has exists (this only affected at debug mode), default 3 and maximum 30.
                    Set into empty value (0/false/null/''/'0'), and it will be set no error to show.
                    or set to -1 to show 30 errors.

## Core Hook

Hook class using WordPress Hook model, The method you could use like a WordPress use

The hooks on Enproject use :

```php
Hook::Apply('fn_name', 'return1', 'arg1', etc...);
```

Like a WordPress use :

```php
apply_filters('fn_name', 'return1', 'arg1', etc...);
```

* `x_before_route`  (void)    Calling Hook before Route Execute

* `x_after_route`   (void)    Calling Hook after Route Running

* `x_headers`       (array)   List Headers (must be not in fatal error)

* `x_header_status` (integer) HTTP Status Code Headers (must be not in fatal error)

* `x_before_output` (string)  Before output render this will result as Output content

* `x_force_tag_output` (string)  Before output render this will result as body content force balance tags result (must be not in fatal error)

* `x_safe_output`  (string)  Before output render this will result as body content entities result (must be not in fatal error)

* `x_error_output` (string)  Before output render this will result as body content that returning error output (if got error)

* `x_after_output` (string)  After output render this will result as body content that returning output

* `x_after_all`    (string)  After All Proccess Done

## Benchmark Shortcode

    * %[benchmark]%      as benchmark total time application execute
    * %[memory]%         as benchmark total memoery application use
    * %[real_memory]%    as benchmark total real memory application use

`to avoid being replace escape use backslash '\' eg : `

`%[\benchmark\]%` will be as `%[benchmark]%`

`%[\\benchmark\\]%` will be as `%[\benchmark\]%`


## Some Features

* Simple add and get application object to singleton

```php

    /**
     * Set Application
     */
    \Aufa\Enproject\Enproject::set('applicationName', new ObjectClass());
    /**
     * Then call
     */
    \Aufa\Enproject\Enproject::get('applicationName');
    /**
     * Or if you have variable to set application
     */
    $application = \Aufa\Enproject\Enproject::Singleton(); // call instance
    /**
     * Call your set application
     */
    $yourapp = $application->applicationName;
```

* You can protect it easily after set

```php

    /**
     * Set Application and protect it, to prevent application being override
     * nb:
     * if application has exists and protected , user warning has been logged.
     * and if you have set debug to true it will be insert on your html
     */
    \Aufa\Enproject\Enproject::set('applicationName', new ObjectClass())->protect();
```

* Call Enproject Core easily

```php

    /**
     * Example call Helper
     */
    $application = \Aufa\Enproject\Enproject::Singleton(); // call instance
    $helper = $application->helper; // get object Helper
    /**
     * Or example if want call string helper
     */
    $string_helper = $application->helper->string;
    /**
     * Or wanna call Request Class
     */
    $request = $application->request;
    /**
     * Or calling class using another method?
     * just like :
     * $application->helper->string
     */
    $string_helper = $application->helper_string;
    // or
    $string_helper = $application->get("Helper\\String");
    // or
    $string_helper = $application->get("Helper_String");
```

* Template Class Ready, with calling `\Aufa\Enproject\Helper\Template::init()`,
    Make sure before calling init make sure you have set template configs parameter.
    see `src/Enproject/Helper/Template.php` . Documentation is On file (see part of class property)

* PasswordHash [phpass](http://www.openwall.com/phpass/) by openwall with custom structures,
    The returning value still same and has no include as singleton.
    So if want to use it as Core Application just add it to application.

## Requirements

* PHP 5.3.2x or later
