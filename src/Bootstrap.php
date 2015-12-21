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

/**
 * PSR Autoloader
 */
return spl_autoload_register(function ($className) {
    /**
     * Determine Base Directory
     */
    $baseDir = function_exists('realpath') ? realpath(__DIR__) : __DIR__;
    $baseDir = $baseDir ? $baseDir : __DIR__;
    $baseDir = $baseDir.'/Enproject/';

    // project-specific namespace prefix
    $prefix  = 'Aufa\\Enproject\\';
    // does the class use the namespace prefix?
    $len = strlen($prefix);
    $className = ltrim($className, '\\');
    if (strncmp($prefix, $className, $len) !== 0) {
        // no, move to the next registered autoloader
        return;
    }

    $className = substr($className, $len);
    $nameSpace = '';
    if ($lastNsPos = strripos($className, '\\')) {
        $namespace = str_replace('\\', '/', $className);
        $namespace = substr($namespace, 0, $lastNsPos);
        $className = substr($className, $lastNsPos + 1);
        if (is_dir($baseDir. $namespace . '/')) {
            $baseDir .= $namespace . '/';
        } else {
            if (is_dir($baseDir . strtoupper($namespace.'/'))) {
                $baseDir .= strtoupper($namespace) . '/';
            } elseif (is_dir($baseDir . ucwords($namespace.'/'))) {
                $baseDir .= ucwords($namespace) . '/';
            }
        }
    }

    /**
     * Fix File for
     */
    if (file_exists($baseDir . $className . '.php')) {
        require_once($baseDir . $className . '.php');
    } elseif (file_exists($baseDir . ucwords($className) . '.php')) {
        require_once($baseDir . ucwords($className) . '.php');
    } elseif (strpos($className, '_') !== false) {
        $classNamearr = explode('_', $className);
        $classNamearr = array_map('ucwords', $classNamearr);
        $className = implode('/', $classNamearr);
        if (file_exists($baseDir . $className . '.php')) {
            require_once($baseDir . $className . '.php');
        }
    }
});
