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

namespace Aufa\Enproject\Abstracts;

/**
 * Crypt Hash Utility
 */
abstract class CryptUtil extends Singleton
{
    /**
     * Fills the zero values
     * @return int
     */
    public static function zeroFill($a, $b)
    {
        $z = hexdec(80000000);
        if ($z & $a) {
            $a = ($a>>1);
            $a &= (~$z & 0xffffffff);
            $a |= 0x40000000;
            $a = ($a >> ($b-1));
        } else {
            $a = ($a >> $b);
        }
        return $a;
    }

    /**
     * split a byte-string into integer array values
     * @return string
     */
    public static function Byte2intSplit($input)
    {
        $l = strlen($input);
        if ($l <= 0) {
            // right...
            return 0;
        } elseif (($l % 4) != 0) {
            // invalid input
            return false;
        }
        $result = array();
        for ($i = 0; $i < $l; $i += 4) {
            $intbuild  = (ord($input[$i]) << 24);
            $intbuild += (ord($input[$i+1]) << 16);
            $intbuild += (ord($input[$i+2]) << 8);
            $intbuild += (ord($input[$i+3]));
            $result[] = $intbuild;
        }

        return $result;
    }

    /**
     * Abstract Create Hash
     * @param [type] $str [description]
     */
    public static function Create($str)
    {
        return static::hash($str);
    }

    /**
     * Default hash method
     * @param  string $string input string tobe hash
     * @return string         hash
     */
    public static function hash($string)
    {
    }
}
