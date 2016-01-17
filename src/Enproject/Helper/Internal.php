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

namespace Aufa\Enproject\Helper;

use Aufa\Enproject\Abstracts\Singleton;

/**
 * Another Function helper for PHP Extended Usage
 */
class Internal extends Singleton
{
    /**
     * Deep mixed string or array to lowercase ( array keys also array values )
     *
     * @param  mixed $value  as to lower content
     * @param  bool  $usekey if as array use key true to make it lower also
     * @return mixed $result
     */
    public static function strtoLower($value, $usekey = false)
    {
        if (is_array($value)) {
            # else if value is array
            // reset result
            // split it with loop
            foreach ($value as $key => $val) {
                $key = $usekey ? self::strtoLower($key) : $key;
                $value[$key] = self::strtoLower($val); # callback to self function
            }
        }
        if (is_object($value)) {
            foreach (get_object_vars($value) as $key => $val) {
                $value->{$key} = self::strtoLower($val); # callback to self function
            }
        }
        if (is_string($value)) {
            $value = strtolower($value);
        }

        return $value;
    }

    /**
     * Deep mixed string or array to uppercase ( array keys also array values )
     *
     * @param  mixed $value  as to lower content
     * @param  bool  $usekey if as array use key true to make it upper also
     * @return mixed $result
     */
    public static function strtoUpper($value, $usekey = false)
    {
        if (is_array($value)) {
            # else if value is array
            // reset result
            // split it with loop
            foreach ($value as $key => $val) {
                $key = $usekey ? self::strtoUpper($key) : $key;
                $value[$key] = self::strtoUpper($val); # callback to self function
            }
        }
        if (is_object($value)) {
            foreach (get_object_vars($value) as $key => $val) {
                $value->{$key} = self::strtoUpper($val); # callback to self function
            }
        }

        if (is_string($value)) {
            $value = strtoupper($value);
        }

        return $value;
    }

    /**
     * Deep str_replace
     * alternative for str replace for array values
     *
     * @param  string             $search   for the target on $object
     * @param  string             $replacer $replace the target search match
     * @param  mixed array|string $object   target for replace
     * @return mixed              str_replace deep
     */
    public static function strReplace($search, $replacer, $object)
    {
        if (! $object) {
            return $object;
        }
        if (is_array($object)) {
            # else if value is array
            // reset result
            // split it with loop
            foreach ($object as $key => $val) {
                $object[$key] = self::strReplace($search, $replacer, $val); # callback to self function
            }
        }
        if (is_object($value)) {
            foreach (get_object_vars($object) as $key => $val) {
                $object->{$key} = self::strReplace($search, $replacer, $val); # callback to self function
            }
        }
        if (is_string($value) || ! is_numeric($value)) {
            $object = str_replace($search, $replacer, $object);
        }

        return $object;
    }

    /**
     * str_split() [php function aliases]
     */
    public static function strSplit($string, $split_length = 1)
    {
        if (function_exists('str_split')) {
            return str_split($string, $split_length);
        }

        $sign = (($split_length < 0) ? -1 : 1);
        $strlen = strlen($string);
        $split_length = abs($split_length);
        if (($split_length == 0) || ($strlen == 0)) {
            $result = false;
        } elseif ($split_length >= $strlen) {
            $result[] = $string;
        } else {
            $length = $split_length;
            for ($i = 0; $i < $strlen; $i++) {
                $i = (($sign < 0) ? $i + $length : $i);
                $result[] = substr($string, $sign*$i, $length);
                $i--;
                $i = (($sign < 0) ? $i : $i + $length);
                if (($i + $split_length) > ($strlen)) {
                    $length = $strlen - ($i + 1);
                } else {
                    $length = $split_length;
                }
            }
        }

        return $result;
    }

    /**
     * Navigates through an array and removes slashes from the values.
     *
     * If an array is passed, the array_map() function causes a callback to pass the
     * value back to the function. The slashes from this value will removed.
     *
     * @param  mixed $value The value to be stripped.
     * @return mixed Stripped value.
     */
    public static function stripslashes($value)
    {
        if (is_array($value)) {
            foreach ($value as $key => $val) {
                $value[$key] = self::stripslashes($val); # callback to self function
            }
        }
        if (is_object($value)) {
            foreach (get_object_vars($value) as $key => $data) {
                $value->{$key} = self::stripslashes($data);
            }
        }
        if (is_string($value)) {
            $value = stripslashes($value);
        }

        return $value;
    }

    /**
     * Decode URL Deep
     *
     * @param  string|array $value as value
     * @return mixed        decoded URL value
     */
    public static function urlDecode($value)
    {
        if (is_array($value)) {
            foreach ($value as $key => $val) {
                $value[$key] = self::urlDecode($val); # callback to self function
            }
        }
        if (is_object($value)) {
            foreach (get_object_vars($value) as $key => $data) {
                $value->{$key} = self::urlDecode($data);
            }
        }

        if (is_string($value)) {
            $value = urldecode($value);
            if (strpos($value, '%2') !== false) {
                $value = self::urlDecode($value);
            }
        }

        return $value;
    }

    /**
     * Encode URL Deep
     *
     * @param  string|array $value as value
     * @return mixed        encoded URL value
     */
    public static function urlEncode($value)
    {
        if (is_array($value)) {
            foreach ($value as $key => $val) {
                $value[$key] = self::urlEncode($val); # callback to self function
            }
        }
        if (is_object($value)) {
            foreach (get_object_vars($value) as $key => $data) {
                $value->{$key} = self::urlEncode($data);
            }
        }

        if (is_string($value)) {
            $value = urlencode($value);
        }

        return $value;
    }

    /**
     * Encode URL Deep
     *
     * @param  string|array $value as value
     * @return mixed        raw encoded URL value
     */
    public static function rawUrlEncode($value)
    {
        if (is_array($value)) {
            foreach ($value as $key => $val) {
                $value[$key] = self::rawurlencode($val); # callback to self function
            }
        }
        if (is_object($value)) {
            foreach (get_object_vars($value) as $key => $data) {
                $value->{$key} = self::rawurlencode($data);
            }
        }

        if (is_string($value)) {
            $value = rawurlencode($value);
        }

        return $value;
    }

    /* --------------------------------------------------------------------------------*
     |                                  Encryption                                     |
     |---------------------------------------------------------------------------------|
     */

    /**
     * Encode the values using base64_encode and replace some string
     * and could decode @uses safe_b64decode()
     *
     * @param  string $string
     * @return string
     */
    public static function safeBase64Encode($string)
    {
        $data = static::base64Encode($string);
        $data = str_replace(array('+', '/', '='), array('-', '_', ''), $data);

        return $data;
    }

    /**
     * Decode the safe_b64encode() of the string values
     *
     * @see safe_b64encode()
     *
     * @param  string $string
     * @return string
     */
    public static function safeBase64Decode($string)
    {
        $data = str_replace(array('-', '_'), array('+', '/'), $string);
        $mod4 = strlen($data) % 4;
        if ($mod4) {
            $data .= substr('====', $mod4);
        }

        return static::base64Decode($data);
    }

    /**
     * Encode 64 Function as alternate function of base64_encode() if not exists
     *
     * @param  string $string
     * @return string
     */
    public static function base64Encode($string = '')
    {
        if (is_array($string) || is_object($string)) {
            $type   = gettype($string);
            $caller =  next(debug_backtrace());
            $eror['line']  = $caller['line'];
            $eror['file']  = strip_tags($caller['file']);
            $error['type'] = E_USER_ERROR;
            trigger_error(
                "base64Encode() expects parameter 1 to be string, "
                . $type
                . " given in <b>{$file}</b> on line <b>{$line}</b><br />\n",
                E_USER_ERROR
            );

            return;
        }
        /**
         * Use Internal
         */
        if (function_exists('base64_encode')) {
            return base64_encode($string);
        }
        if (strlen($string) <= 0) {
            return '';
        }

        $binval  = static::str2bin($string);
        $final = "";
        $start = 0;
        while ($start < strlen($binval)) {
            if (strlen(substr($binval, $start)) < 6) {
                $binval .= str_repeat("0", 6 - strlen(substr($binval, $start)));
            }
            $tmp = bindec(substr($binval, $start, 6));
            if ($tmp < 26) {
                $final .= chr($tmp + 65);
            } elseif ($tmp > 25 && $tmp < 52) {
                $final .= chr($tmp + 71);
            } elseif ($tmp == 62) {
                $final .= "+";
            } elseif ($tmp == 63) {
                $final .= "/";
            } elseif (!$tmp) {
                $final .= "A";
            } else {
                $final .= chr($tmp - 4);
            }
            $start += 6;
        }
        if (strlen($final) % 4 > 0) {
            $final .= str_repeat("=", 4 - strlen($final) % 4);
        }

        return $final;
    }

    /**
     * Decode 64 Function as alternate function of base64_decode() if not exists
     *     Maybe some result it will be different for some case
     *
     * @param  string $string
     * @return string
     */
    public static function base64Decode($string)
    {
        if (is_array($string) || is_object($string)) {
            $type   = gettype($string);
            $caller =  next(debug_backtrace());
            $eror['line']  = $caller['line'];
            $eror['file']  = strip_tags($caller['file']);
            $error['type'] = E_USER_ERROR;
            trigger_error(
                "base64Decode() expects parameter 1 to be string, "
                . $type
                . " given in <b>{$file}</b> on line <b>{$line}</b><br />\n",
                E_USER_ERROR
            );

            return;
        }

        /**
         * Use Internal
         */
        if (function_exists('base64_decode')) {
            return base64_decode($string);
        }

        if (strlen($string) <= 0) {
            return '';
        }

        $keyStr = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=";
        $chr1 = $chr2 = $chr3 = "";
        $enc1 = $enc2 = $enc3 = $enc4 = "";
        $i = 0;
        $output = "";
        // remove all characters that are not A-Z, a-z, 0-9, +, /, or =
        $string = preg_replace("/[^A-Za-z0-9\+\/\=]/", "", $string);
        do {
            $enc1 = strpos($keyStr, substr($string, $i++, 1));
            $enc2 = strpos($keyStr, substr($string, $i++, 1));
            $enc3 = strpos($keyStr, substr($string, $i++, 1));
            $enc4 = strpos($keyStr, substr($string, $i++, 1));
            $chr1 = ($enc1 << 2) | ($enc2 >> 4);
            $chr2 = (($enc2 & 15) << 4) | ($enc3 >> 2);
            $chr3 = (($enc3 & 3) << 6) | $enc4;
            $output = $output . chr((int) $chr1);
            if ($enc3 != 64) {
                $output = $output . chr((int) $chr2);
            }
            if ($enc4 != 64) {
                $output = $output . chr((int) $chr3);
            }
            $chr1 = $chr2 = $chr3 = "";
            $enc1 = $enc2 = $enc3 = $enc4 = "";
        } while ($i < strlen($string));

        return urldecode($output);
    }

    /* --------------------------------------------------------------------------------*
     |                              Binary Convertion                                  |
     |---------------------------------------------------------------------------------|
     */

    /**
     * Converting string into binary
     *
     * @param  string $string the string to convert
     * @return string
     */
    public static function str2bin($string)
    {
        if (is_array($string) || is_object($string)) {
            $type   = gettype($string);
            $caller =  next(debug_backtrace());
            $eror['line']  = $caller['line'];
            $eror['file']  = strip_tags($caller['file']);
            $error['type'] = E_USER_ERROR;
            trigger_error(
                "str2bin() expects parameter 1 to be string, "
                . $type
                . " given in <b>{$file}</b> on line <b>{$line}</b><br />\n",
                E_USER_ERROR
            );

            return;
        }

        if (strlen($string) <= 0) {
            return;
        }

        $string = static::strSplit($string, 1);
        for ($i = 0, $n = count($string); $i < $n; ++$i) {
            $string[$i] = decbin(ord($string[$i]));
            $string[$i] = str_repeat("0", 8 - strlen($string[$i])) . $string[$i];
        }
        return implode("", $string);
    }

    /**
     * Converting binary string into normal string
     *
     * @param  string $string the string to convert
     * @return string
     */
    public static function bin2str($string)
    {
        if (is_array($string) || is_object($string)) {
            $type   = gettype($string);
            $caller =  next(debug_backtrace());
            $eror['line']  = $caller['line'];
            $eror['file']  = strip_tags($caller['file']);
            $error['type'] = E_USER_ERROR;
            trigger_error(
                "bin2str() expects parameter 1 to be string, "
                . $type
                . " given in <b>{$file}</b> on line <b>{$line}</b><br />\n",
                E_USER_ERROR
            );

            return;
        }

        if (strlen($string) <= 0) {
            return;
        }

        $string = static::strSplit($string, 8); // NOTE: this function is PHP5 only
        for ($i = 0, $n = count($string); $i < $n; ++$i) {
            $string[$i] = chr(bindec($string[$i]));
        }

        return implode('', $string);
    }

    /* --------------------------------------------------------------------------------*
     |                              Extended Helpers                                   |
     |---------------------------------------------------------------------------------|
     */

    /**
     * Rotate each string characters by n positions in ASCII table
     * To encode use positive n, to decode - negative.
     * With n = 13 (ROT13), encode and decode n can be positive.
     * @see  {@link http://php.net/str_rot13}
     *
     * @param  string  $string
     * @param  integer $n
     * @return string
     */
    public static function rotate($string, $n = 13)
    {
        $length = strlen($string);
        $result = '';

        for ($i = 0; $i < $length; $i++) {
            $ascii = ord($string{$i});

            $rotated = $ascii;

            if ($ascii > 64 && $ascii < 91) {
                $rotated += $n;
                $rotated > 90 && $rotated += -90 + 64;
                $rotated < 65 && $rotated += -64 + 90;
            } elseif ($ascii > 96 && $ascii < 123) {
                $rotated += $n;
                $rotated > 122 && $rotated += -122 + 96;
                $rotated < 97 && $rotated  += -96 + 122;
            }

            $result .= chr($rotated);
        }

        return $result;
    }

    /**
     * Check PHP Version
     * @param  string $version php version to check
     * @return bool   true if match version compare current or more
     */
    public static function isPhp($version = null)
    {
        static $php_version = array();
        if (!($version)) {
            return PHP_VERSION;
        }

        $version = (string) $version;
        if (! isset($php_version[$version])) {
            $php_version[$version] = version_compare(PHP_VERSION, $version, '>=');
        }

        return $php_version[$version];
    }

    /* --------------------------------------------------------------------------------*
     |                              Path & File Helpers                                |
     |---------------------------------------------------------------------------------|
     */

    /**
     * Check if path is file
     *
     * @param  string  $path path to be check
     * @return boolean       true if it is file
     */
    public static function isFile($path)
    {
        return is_file($path);
    }

    /**
     * Check if current path is directory
     *
     * @param  string  $path directory
     * @return boolean      true if is directory
     */
    public static function isDir($path)
    {
        return is_dir($path);
    }

    /**
     * Check if current path is writable
     *
     * @param  string  $path path to be check
     * @return boolean       true if writable
     */
    public static function isWritable($path)
    {
        return is_writable($path);
    }

    /**
     * Chmod Function to set permission of files
     *
     * @param  string  $path path to be set
     * @param  integer $mode chmod mode
     */
    public static function chmod($path, $mode)
    {
        if (is_dir($path) || is_file($file)) {
            return @chmod($path, $mode);
        }
    }

    /**
     * Tests for file writability
     *
     * is_writable() returns TRUE on Windows servers when you really can't write to
     * the file, based on the read-only attribute. is_writable() is also unreliable
     * on Unix servers if safe_mode is on.
     *
     * @credits CI (Code Igniter)
     *
     * @link    https://bugs.php.net/bug.php?id=54709
     * @param   string  $file filepath
     * @param   bool    $force  to determine recheck true if want to recheck
     * @return  bool
     */
    public static function isReallyWritable($file, $force = false)
    {
        if (!is_string($file)) {
            return false;
        }
        // cached result
        static $retval = array();
        $key = md5($file);
        if (isset($retval[$key]) && !$force) {
            return $retval[$key];
        }
        // If we're on a Unix server with safe_mode off we call is_writable
        if (DIRECTORY_SEPARATOR === '/' && (static::isPhp('5.4') || ! @ini_get('safe_mode'))) {
            $retval[$key] = static::isWritable($file);
            return $retval[$key];
        }

        /* For Windows servers and safe_mode "on" installations we'll actually
         * write a file then read it. Bah...
         */
        if (static::isDir($file)) {
            $file = Path::cleanPath($file);
            // file random
            $file .='/'.md5(mt_rand());
            if (($fp = @fopen($file, 'ab')) === false) {
                return false;
            }

            fclose($fp);
            static::chmod($file, 0777);
            @unlink($file);
            unset($file);
            $retval[$key] = true;
            return $retval[$key];
        } elseif (! is_file($file) || ($fp = @fopen($file, 'ab')) === false) {
            $retval[$key] = false;
            return $retval[$key];
        }

        $fp && fclose($fp); // close resource if resource is opened
        return $retval[$key];
    }

    /**
     * Read Directory Nested
     *
     * @param  string  $path            Path directory to be scan
     * @param  integer $directory_depth directory depth of nested to be scanned
     * @param  boolean $hidden          true if want to show hidden content
     * @return array                    path trees
     */
    public static function readDirList($path, $directory_depth = 0, $hidden = false)
    {
        $filedata = false;
        if (static::isDir($path) && $fp = opendir($path)) {
            $new_depth  = $directory_depth - 1;
            $path = Path::cleanPath($path).'/';
            while (false !== ($file = readdir($fp))) {
                // Remove '.', '..', and hidden files [optional]
                if ($file === '.' || $file === '..' || ($hidden === false && $file[0] === '.')) {
                    continue;
                }

                static::isDir($path.$file) && $path .= '/';

                if (($directory_depth < 1 || $new_depth > 0) &&  static::isDir($path.$file)) {
                    $filedata[$file] = static::readDirList($path.$file, $new_depth, $hidden);
                } else {
                    $filedata[] = $file;
                }
            }
            // close resource
            closedir($fp);
        }
        return $filedata;
    }

    /**
     * Getting content from file if exists
     *
     * @param  string $file    full path into file
     * @param  mixed  $default default return if no result
     * @return string output
     */
    public static function getContent($file, $default = null)
    {
        $content = '';
        if (static::isFile($file) && $fp = fopen($file, 'r')) {
            while ($data = fread($fp, 4096)) {
                $content .= $data;
            }
            fclose($fp);
            unset($data, $default);
            return $content;
        }
        return $default;
    }
}
