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
 * MD5 hash Algorithm
 */
class Md5 extends CryptUtil
{
    /**
     * Hashing md5 from input string
     * @param  string $string value input to be hashed
     * @return string         hashed string result
     */
    public static function hash($string)
    {
        if (function_exists('md5')) {
            return md5($string);
        }

        if (is_array($string) || is_object($string)) {
            $type   = gettype($string);
            $caller =  next(debug_backtrace());
            $eror['line']  = $caller['line'];
            $eror['file']  = strip_tags($caller['file']);
            $error['type'] = E_USER_ERROR;
            trigger_error(
                "md5() expects parameter 1 to be string, "
                . $type
                . " given in <b>{$file}</b> on line <b>{$line}</b><br />\n",
                E_USER_ERROR
            );

            return;
        }

        // convert into string
        $string = "{$string}";
        $instance = self::singleton();
        $A = "67452301";
        $a = $A;
        $B = "efcdab89";
        $b = $B;
        $C = "98badcfe";
        $c = $C;
        $D = "10325476";
        $d = $D;
        $words = $instance->binArray($string);
        for ($i = 0; $i <= count($words)/16-1; $i++) {
            $a = $A;
            $b = $B;
            $c = $C;
            $d = $D;
            /* ROUND 1 */
            $a = $instance->pad($a, $b, $c, $d, $words[0 + ($i * 16)], 7, "d76aa478", '1');
            $d = $instance->pad($d, $a, $b, $c, $words[1 + ($i * 16)], 12, "e8c7b756", '1');
            $c = $instance->pad($c, $d, $a, $b, $words[2 + ($i * 16)], 17, "242070db", '1');
            $b = $instance->pad($b, $c, $d, $a, $words[3 + ($i * 16)], 22, "c1bdceee", '1');
            $a = $instance->pad($a, $b, $c, $d, $words[4 + ($i * 16)], 7, "f57c0faf", '1');
            $d = $instance->pad($d, $a, $b, $c, $words[5 + ($i * 16)], 12, "4787c62a", '1');
            $c = $instance->pad($c, $d, $a, $b, $words[6 + ($i * 16)], 17, "a8304613", '1');
            $b = $instance->pad($b, $c, $d, $a, $words[7 + ($i * 16)], 22, "fd469501", '1');
            $a = $instance->pad($a, $b, $c, $d, $words[8 + ($i * 16)], 7, "698098d8", '1');
            $d = $instance->pad($d, $a, $b, $c, $words[9 + ($i * 16)], 12, "8b44f7af", '1');
            $c = $instance->pad($c, $d, $a, $b, $words[10 + ($i * 16)], 17, "ffff5bb1", '1');
            $b = $instance->pad($b, $c, $d, $a, $words[11 + ($i * 16)], 22, "895cd7be", '1');
            $a = $instance->pad($a, $b, $c, $d, $words[12 + ($i * 16)], 7, "6b901122", '1');
            $d = $instance->pad($d, $a, $b, $c, $words[13 + ($i * 16)], 12, "fd987193", '1');
            $c = $instance->pad($c, $d, $a, $b, $words[14 + ($i * 16)], 17, "a679438e", '1');
            $b = $instance->pad($b, $c, $d, $a, $words[15 + ($i * 16)], 22, "49b40821", '1');

            /* round 2 */
            $a = $instance->pad($a, $b, $c, $d, $words[1 + ($i * 16)], 5, "f61e2562", '2');
            $d = $instance->pad($d, $a, $b, $c, $words[6 + ($i * 16)], 9, "c040b340", '2');
            $c = $instance->pad($c, $d, $a, $b, $words[11 + ($i * 16)], 14, "265e5a51", '2');
            $b = $instance->pad($b, $c, $d, $a, $words[0 + ($i * 16)], 20, "e9b6c7aa", '2');
            $a = $instance->pad($a, $b, $c, $d, $words[5 + ($i * 16)], 5, "d62f105d", '2');
            $d = $instance->pad($d, $a, $b, $c, $words[10 + ($i * 16)], 9, "2441453", '2');
            $c = $instance->pad($c, $d, $a, $b, $words[15 + ($i * 16)], 14, "d8a1e681", '2');
            $b = $instance->pad($b, $c, $d, $a, $words[4 + ($i * 16)], 20, "e7d3fbc8", '2');
            $a = $instance->pad($a, $b, $c, $d, $words[9 + ($i * 16)], 5, "21e1cde6", '2');
            $d = $instance->pad($d, $a, $b, $c, $words[14 + ($i * 16)], 9, "c33707d6", '2');
            $c = $instance->pad($c, $d, $a, $b, $words[3 + ($i * 16)], 14, "f4d50d87", '2');
            $b = $instance->pad($b, $c, $d, $a, $words[8 + ($i * 16)], 20, "455a14ed", '2');
            $a = $instance->pad($a, $b, $c, $d, $words[13 + ($i * 16)], 5, "a9e3e905", '2');
            $d = $instance->pad($d, $a, $b, $c, $words[2 + ($i * 16)], 9, "fcefa3f8", '2');
            $c = $instance->pad($c, $d, $a, $b, $words[7 + ($i * 16)], 14, "676f02d9", '2');
            $b = $instance->pad($b, $c, $d, $a, $words[12 + ($i * 16)], 20, "8d2a4c8a", '2');

            /* round 3 */
            $a = $instance->pad($a, $b, $c, $d, $words[5 + ($i * 16)], 4, "fffa3942", '3');
            $d = $instance->pad($d, $a, $b, $c, $words[8 + ($i * 16)], 11, "8771f681", '3');
            $c = $instance->pad($c, $d, $a, $b, $words[11 + ($i * 16)], 16, "6d9d6122", '3');
            $b = $instance->pad($b, $c, $d, $a, $words[14 + ($i * 16)], 23, "fde5380c", '3');
            $a = $instance->pad($a, $b, $c, $d, $words[1 + ($i * 16)], 4, "a4beea44", '3');
            $d = $instance->pad($d, $a, $b, $c, $words[4 + ($i * 16)], 11, "4bdecfa9", '3');
            $c = $instance->pad($c, $d, $a, $b, $words[7 + ($i * 16)], 16, "f6bb4b60", '3');
            $b = $instance->pad($b, $c, $d, $a, $words[10 + ($i * 16)], 23, "bebfbc70", '3');
            $a = $instance->pad($a, $b, $c, $d, $words[13 + ($i * 16)], 4, "289b7ec6", '3');
            $d = $instance->pad($d, $a, $b, $c, $words[0 + ($i * 16)], 11, "eaa127fa", '3');
            $c = $instance->pad($c, $d, $a, $b, $words[3 + ($i * 16)], 16, "d4ef3085", '3');
            $b = $instance->pad($b, $c, $d, $a, $words[6 + ($i * 16)], 23, "4881d05", '3');
            $a = $instance->pad($a, $b, $c, $d, $words[9 + ($i * 16)], 4, "d9d4d039", '3');
            $d = $instance->pad($d, $a, $b, $c, $words[12 + ($i * 16)], 11, "e6db99e5", '3');
            $c = $instance->pad($c, $d, $a, $b, $words[15 + ($i * 16)], 16, "1fa27cf8", '3');
            $b = $instance->pad($b, $c, $d, $a, $words[2 + ($i * 16)], 23, "c4ac5665", '3');

            /* round 4 */
            $a = $instance->pad($a, $b, $c, $d, $words[0 + ($i * 16)], 6, "f4292244", '4');
            $d = $instance->pad($d, $a, $b, $c, $words[7 + ($i * 16)], 10, "432aff97", '4');
            $c = $instance->pad($c, $d, $a, $b, $words[14 + ($i * 16)], 15, "ab9423a7", '4');
            $b = $instance->pad($b, $c, $d, $a, $words[5 + ($i * 16)], 21, "fc93a039", '4');
            $a = $instance->pad($a, $b, $c, $d, $words[12 + ($i * 16)], 6, "655b59c3", '4');
            $d = $instance->pad($d, $a, $b, $c, $words[3 + ($i * 16)], 10, "8f0ccc92", '4');
            $c = $instance->pad($c, $d, $a, $b, $words[10 + ($i * 16)], 15, "ffeff47d", '4');
            $b = $instance->pad($b, $c, $d, $a, $words[1 + ($i * 16)], 21, "85845dd1", '4');
            $a = $instance->pad($a, $b, $c, $d, $words[8 + ($i * 16)], 6, "6fa87e4f", '4');
            $d = $instance->pad($d, $a, $b, $c, $words[15 + ($i * 16)], 10, "fe2ce6e0", '4');
            $c = $instance->pad($c, $d, $a, $b, $words[6 + ($i * 16)], 15, "a3014314", '4');
            $b = $instance->pad($b, $c, $d, $a, $words[13 + ($i * 16)], 21, "4e0811a1", '4');
            $a = $instance->pad($a, $b, $c, $d, $words[4 + ($i * 16)], 6, "f7537e82", '4');
            $d = $instance->pad($d, $a, $b, $c, $words[11 + ($i * 16)], 10, "bd3af235", '4');
            $c = $instance->pad($c, $d, $a, $b, $words[2 + ($i * 16)], 15, "2ad7d2bb", '4');
            $b = $instance->pad($b, $c, $d, $a, $words[9 + ($i * 16)], 21, "eb86d391", '4');

            $A = $instance->add(
                $instance->hexdec($a),
                $instance->hexdec($A)
            );
            $B = $instance->add(
                $instance->hexdec($b),
                $instance->hexdec($B)
            );
            $C = $instance->add(
                $instance->hexdec($c),
                $instance->hexdec($C)
            );
            $D = $instance->add(
                $instance->hexdec($d),
                $instance->hexdec($D)
            );
        }

        $words = $instance->str2Hex($A)
            . $instance->str2Hex($B)
            . $instance->str2Hex($C)
            . $instance->str2Hex($D);
        unset($a, $b, $c, $d, $A, $B, $C, $D, $string, $instance);
        return $words;
    }

    private function binArray($string)
    {
        $length = strlen($string);
        $n = (((($length + 8)-($length + 8 % 64))/64)+1)*16;
        $words= array();
        $bytePos = $byteCount = 0;
        while ($byteCount < $length) {
            $wordsCount = ($byteCount-($byteCount % 4))/4;
            $bytePos = ($byteCount % 4)*8;
            if (!isset($words[$wordsCount])) {
                $words[$wordsCount] = 0;
            }
            $words[$wordsCount] = ($words[$wordsCount] | (ord($string[$byteCount]) << $bytePos));
            $byteCount++;
        }

        $wordsCount = ($byteCount-($byteCount % 4))/4;
        $words[$wordsCount] = $words[$wordsCount] | (0x80 << (($byteCount % 4)*8));
        $words[$n - 2] = $length << 3;
        $words[$n - 1] = $length >> 29;
        for ($i=0; $i < $n; $i++) {
            $words[$i] = isset($words[$i])? decbin($words[$i]) : "0";
        }
        return $words;
    }

    private function str2Hex($value)
    {
        $hex = '';
        for ($i = 0; $i <= 3; $i++) {
            $byte = ($this->hexdec($value)>>($i*8)) & 255;
            $c    = dechex($byte);
            $hex .= (strlen($c) == '1')? "0". dechex($byte) : dechex($byte);
        }
        return $hex;
    }
       
    private function hexdec($hex, $debug = false)
    {
        if (substr($hex, 0, 1) == "-") {
            return doubleval('-'.hexdec("0x". str_replace("-", "", $hex)));
        }
        return hexdec("0x".$hex);
    }
 
    private function pad($a, $b, $c, $d, $m, $s, $t, $rounds)
    {
        $x = $this->hexdec($b);
        $y = $this->hexdec($c);
        $z = $this->hexdec($d);
        switch ($rounds) {
            case '1':
                $val = (($x & $y) | ((~ $x) & $z));
                break;
            case '2':
                $val = (($x & $z) | ($y & (~ $z)));
                break;
            case '3':
                $val = ($x ^ $y ^ $z);
                break;
            case '4':
                $val = ($y ^ ($x | (~ $z)));
                break;
        }
        if (!isset($val)) {
            return;
        }
        $a = $this->hexdec(
            $this->add(
                $this->hexdec($a),
                $this->hexdec(
                    $this->add(
                        $this->hexdec(
                            $this->add(
                                $val,
                                bindec($m)
                            )
                        ),
                        $this->hexdec($t)
                    )
                )
            )
        );
        return $this->add($this->rotr($a, $s), $this->hexdec($b)) ;
    }

    private function rotr($decimal, $bits, $debug = false)
    {
        return  (($decimal << $bits) | $this->shiftright($decimal, (32 - $bits))  & 0xffffffff);
    }

    private function shiftright($decimal, $right)
    {
        if ($decimal < 0) {
            $res = decbin($decimal >> $right);
            for ($i=0; $i < $right; $i++) {
                $res[$i] = "";
            }
            return bindec($res) ;
        } else {
            return ($decimal >> $right);
        }
    }
     
    private function add($x, $y)
    {
        $x8 = ($x & 0x80000000);
        $y8 = ($y & 0x80000000);
        $x4 = ($x & 0x40000000);
        $y4 = ($y & 0x40000000);
        $result = ($x & 0x3FFFFFFF)+($y & 0x3FFFFFFF);
        if ($x4 & $y4) {
            $res = ($result ^ 0x80000000 ^ $x8 ^ $y8);
            if ($res < 0) {
                return '-'.dechex(abs($res));
            } else {
                return dechex($res);
            }
        }
        if ($x4 | $y4) {
            if ($result & 0x40000000) {
                $res = ($result ^ 0xC0000000 ^ $x8 ^ $y8);
                if ($res < 0) {
                    return '-'.dechex(abs($res));
                } else {
                    return dechex($res);
                }
            } else {
                $res = ($result ^ 0x40000000 ^ $x8 ^ $y8);
                if ($res < 0) {
                    return '-'.dechex(abs($res));
                } else {
                    return dechex($res);
                }
            }
        } else {
            $res = ($result ^ $x8 ^ $y8);
            if ($res < 0) {
                return '-'.dechex(abs($res));
            } else {
                return dechex($res);
            }
        }
    }
}
