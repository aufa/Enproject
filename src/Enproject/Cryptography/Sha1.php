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

namespace Aufa\Enproject\Cryptography;

use Aufa\Enproject\Abstracts\CryptUtil;

/**
 * Sha1 hash Algorithm
 * @see  <http://php.net/manual/en/function.sha1.php#47609>
 */
class Sha1 extends CryptUtil
{
    /* --------------------------------------------------------------------------------*
     |                              Class Properties                                   |
     |---------------------------------------------------------------------------------|
     */

    /**
     * Hash record cache
     * @var array
     */
    private $x_sha1_record = array();

    /* --------------------------------------------------------------------------------*
     |                                Class Method                                     |
     |---------------------------------------------------------------------------------|
     */

    /**
     * PHP5 Constructor
     * Doing add parameters to make hash echoing result
     *
     * @param string $str result hash
     */
    public function __construct($str = null)
    {
        ! is_null($str) && $this->x_sha1_record[$str] = self::hash($str);
    }

    /**
     * Doing hash
     *
     * @param  string $string string to hash
     * @return string         hash result
     */
    public static function hash($string)
    {
        /**
         * Fallback sha1([(string) string]);
         */
        if (function_exists('sha1')) {
            return sha1($string);
        }

        if (is_array($string) || is_object($string)) {
            $type   = gettype($string);
            $caller =  next(debug_backtrace());
            $eror['line']  = $caller['line'];
            $eror['file']  = strip_tags($caller['file']);
            $error['type'] = E_USER_ERROR;
            trigger_error(
                "sha1() expects parameter 1 to be string, "
                . $type
                . " given in <b>{$file}</b> on line <b>{$line}</b><br />\n",
                E_USER_ERROR
            );

            return;
        }

        // convert into string
        $string = "{$string}";
        $instance = self::singleton();
        $key = Md5::hash($string);
        if (isset($instance->x_sha1_record[$key])) {
            return $instance->x_sha1_record[$key];
        }
        $x = $instance->blockString($string);
        $a =  1732584193;
        $b = -271733879;
        $c = -1732584194;
        $d =  271733878;
        $e = -1009589776;
       
        $x_count = count($x);
       
        for ($i = 0; $i < $x_count; $i += 16) {
            $olda = $a;
            $oldb = $b;
            $oldc = $c;
            $oldd = $d;
            $olde = $e;
           
            for ($j = 0; $j < 80; $j++) {
                $w[$j] = ($j < 16) ? $x[$i + $j] : $instance->rotr($w[$j - 3] ^ $w[$j - 8] ^ $w[$j - 14] ^ $w[$j - 16], 1);
               
                $t = $instance->add(
                    $instance->add(
                        $instance->rotr($a, 5),
                        $instance->rotf($j, $b, $c, $d)
                    ),
                    $instance->add(
                        $instance->add($e, $w[$j]),
                        $instance->sha1Kt($j)
                    )
                );
                $e = $d;
                $d = $c;
                $c = $instance->rotr($b, 30);
                $b = $a;
                $a = $t;
            }
           
            $a = $instance->add($a, $olda);
            $b = $instance->add($b, $oldb);
            $c = $instance->add($c, $oldc);
            $d = $instance->add($d, $oldd);
            $e = $instance->add($e, $olde);
        }
        $instance->x_sha1_record[$key] = sprintf('%08x%08x%08x%08x%08x', $a, $b, $c, $d, $e);
        $retval = $instance->x_sha1_record[$key];
        unset($instance, $string, $x, $a, $b, $c, $d, $t, $w);
        return $retval;
    }

    private function add($x, $y)
    {
        $lsw = ($x & 0xFFFF) + ($y & 0xFFFF);
        $msw = ($x >> 16) + ($y >> 16) + ($lsw >> 16);

        return ($msw << 16) | ($lsw & 0xFFFF);
    }

    private function blockString($str)
    {
        $strlen_str = strlen($str);
        $nblk = (($strlen_str + 8) >> 6) + 1;
        for ($i=0; $i < $nblk * 16; $i++) {
            $blks[$i] = 0;
        }
        for ($i=0; $i < $strlen_str; $i++) {
            $blks[$i >> 2] |= ord(substr($str, $i, 1)) << (24 - ($i % 4) * 8);
        }

        $blks[$i >> 2] |= 0x80 << (24 - ($i % 4) * 8);
        $blks[$nblk * 16 - 1] = $strlen_str * 8;
        return $blks;
    }

    private function rotr($num, $cnt)
    {
        return ($num << $cnt) | parent::zeroFill($num, 32 - $cnt);
    }

    private function rotf($t, $b, $c, $d)
    {
        if ($t < 20) {
            return ($b & $c) | ((~$b) & $d);
        }
        if ($t < 40) {
            return $b ^ $c ^ $d;
        }
        if ($t < 60) {
            return ($b & $c) | ($b & $d) | ($c & $d);
        }
       
        return $b ^ $c ^ $d;
    }

    private function sha1Kt($t)
    {
        if ($t < 20) {
            return 1518500249;
        }
        if ($t < 40) {
            return 1859775393;
        }
        if ($t < 60) {
            return -1894007588;
        }
       
        return -899497514;
    }

    /* --------------------------------------------------------------------------------*
     |                                Overloading                                      |
     |---------------------------------------------------------------------------------|
     */

    /**
     * Php5 Magic Method Echoing Object
     * @return string   end sha1 cache
     */
    public function __toString()
    {
        $retval = end($this->x_sha1_record);
        return "{$retval}";
    }

    /**
     * Destruct or end of object called & render
     */
    public function __destruct()
    {
        $this->x_sha1_record = array();
    }
}
