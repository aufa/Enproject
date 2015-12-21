<?php
/**
 * Portable PHP password hashing framework.
 *
 * Version 0.3 / genuine.
 *
 * Written by Solar Designer <solar at openwall.com> in 2004-2006 and placed in
 * the public domain.  Revised in subsequent years, still public domain.
 *
 * There's absolutely no warranty.
 *
 * The homepage URL for this framework is:
 *
 *   http://www.openwall.com/phpass/
 *
 * Please be sure to update the Version line if you edit this file in any way.
 * It is suggested that you leave the main version number intact, but indicate
 * your project name (after the slash) and add your own revision information.
 *
 * Please do not change the "private" password hashing method implemented in
 * here, thereby making your hashes incompatible.  However, if you must, please
 * change the hash type identifier (the "$P$") to something different.
 *
 * Obviously, since this code is in the public domain, the above are not
 * requirements (there can be none), but merely suggestions.
 * -------------------------------------------------------------
 *
 * @version 0.3-rev2    Edited from Original Version <Version 0.3 - Genuine> of phpass
 *                      Complete OOP php5 structural
 * @package aufa\enproject
 *
 */

namespace Aufa\Enproject;

/**
 * The original file consist take from <http://www.openwall.com/phpass/>
 *     that custom change eg: change of method name and class
 *     The license is follow of phpass license.
 *     We are not change the code hardly, make it work with change method
 *     add the bracket ({/}) and make the code neat and documented and PSR4 Compatible
 */
class PasswordHash
{
    /**
     * [$itoa64 description]
     * @var string
     */
    private $x_itoa64;

    /**
     * Iteration count
     * @var integer
     */
    private $x_iteration_count_log2;

    /**
     * is portable hash
     * @var boolean
     */
    private $x_portable_hashes;

    /**
     * Random float state used by hashes
     * @var string
     */
    private $x_random_state;

    /**
     * PHP 5 Constructor
     *
     * @param integer $iteration_count_log2 iteration count
     * @param boolean $portable_hashes      portable has or no (false recommended)
     */
    public function __construct($iteration_count_log2 = 8, $portable_hashes = false)
    {
        $this->x_itoa64 = './0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';

        if ($iteration_count_log2 < 4 || $iteration_count_log2 > 31) {
            $iteration_count_log2 = 8;
        }

        $this->x_iteration_count_log2 = $iteration_count_log2;
        $this->x_portable_hashes = $portable_hashes;
        $this->x_random_state = microtime();

        if (function_exists('getmypid')) {
            $this->x_random_state .= getmypid();
        }
    }

    /**
     * Getting random bytes
     *
     * @param  integer $count count random
     * @access private internal use only
     * @return string
     */
    private function getRandomBytes($count)
    {
        $output = '';
        for ($i = 0; $i < $count; $i += 16) {
            $this->x_random_state = md5(microtime() . $this->x_random_state);
            $output .= pack('H*', md5($this->x_random_state));
        }
        $output = substr($output, 0, $count);

        return $output;
    }

    /**
     * Base 64 Encoded base count iteration
     *
     * @param  string $input string to encode
     * @param  integer $count count iteration
     * @access private internal use only
     * @return string
     */
    private function encode64($input, $count)
    {
        $output = '';
        $i = 0;
        do {
            $value = ord($input[$i++]);
            $output .= $this->x_itoa64[$value & 0x3f];
            if ($i < $count) {
                $value |= ord($input[$i]) << 8;
            }

            $output .= $this->x_itoa64[($value >> 6) & 0x3f];
            if ($i++ >= $count) {
                break;
            }
            if ($i < $count) {
                $value |= ord($input[$i]) << 16;
            }

            $output .= $this->x_itoa64[($value >> 12) & 0x3f];
            if ($i++ >= $count) {
                break;
            }

            $output .= $this->x_itoa64[($value >> 18) & 0x3f];
        } while ($i < $count);

        return $output;
    }

    /**
     * generate private salt
     *
     * @param  string $input string to be generate salt
     * @access private internal use only
     * @return string
     */
    private function gensaltPrivate($input)
    {
        $output = '$P$';
        $output .= $this->x_itoa64[min($this->x_iteration_count_log2 +
            ((PHP_VERSION >= '5') ? 5 : 3), 30)];
        $output .= $this->encode64($input, 6);

        return $output;
    }

    /**
     * Encrypt private password
     *
     * @param  string $password the password
     * @param  string $setting  salt private
     * @access private internal use only
     * @return string
     */
    private function cryptPrivate($password, $setting)
    {
        $output = '*0';
        if (substr($setting, 0, 2) == $output) {
            $output = '*1';
        }

        $id = substr($setting, 0, 3);
        # We use "$P$", phpBB3 uses "$H$" for the same thing
        if ($id != '$P$' && $id != '$H$') {
            return $output;
        }

        $count_log2 = strpos($this->x_itoa64, $setting[3]);
        if ($count_log2 < 7 || $count_log2 > 30) {
            return $output;
        }

        $count = 1 << $count_log2;

        $salt = substr($setting, 4, 8);
        if (strlen($salt) != 8) {
            return $output;
        }

        # We're kind of forced to use MD5 here since it's the only
        # cryptographic primitive available in all versions of PHP
        # currently in use.  To implement our own low-level crypto
        # in PHP would result in much worse performance and
        # consequently in lower iteration counts and hashes that are
        # quicker to crack (by non-PHP code).
        if (PHP_VERSION >= '5') {
            $hash = md5($salt . $password, true);
            do {
                $hash = md5($hash . $password, true);
            } while (--$count);
        } else {
            $hash = pack('H*', md5($salt . $password));
            do {
                $hash = pack('H*', md5($hash . $password));
            } while (--$count);
        }

        $output = substr($setting, 0, 12);
        $output .= $this->encode64($hash, 16);

        return $output;
    }

    /**
     * Generate extended salt string
     *
     * @param  string $input to be generate
     * @access private internal use only
     * @return string
     */
    private function gensaltExtended($input)
    {
        $count_log2 = min($this->x_iteration_count_log2 + 8, 24);
        # This should be odd to not reveal weak DES keys, and the
        # maximum valid value is (2**24 - 1) which is odd anyway.
        $count = (1 << $count_log2) - 1;

        $output = '_';
        $output .= $this->x_itoa64[$count & 0x3f];
        $output .= $this->x_itoa64[($count >> 6) & 0x3f];
        $output .= $this->x_itoa64[($count >> 12) & 0x3f];
        $output .= $this->x_itoa64[($count >> 18) & 0x3f];
        $output .= $this->encode64($input, 3);

        return $output;
    }

    /**
     * generating Salt with blowfish method
     *
     * @param  string $input to generate
     * @access private internal use only
     * @return string
     */
    private function gensaltBlowfish($input)
    {
        # This one needs to use a different order of characters and a
        # different encoding scheme from the one in encode64() above.
        # We care because the last character in our encoded string will
        # only represent 2 bits.  While two known implementations of
        # bcrypt will happily accept and correct a salt string which
        # has the 4 unused bits set to non-zero, we do not want to take
        # chances and we also do not want to waste an additional byte
        # of entropy.
        $itoa64 = './ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
        $output = '$2a$';
        $output .= chr(ord('0') + $this->x_iteration_count_log2 / 10);
        $output .= chr(ord('0') + $this->x_iteration_count_log2 % 10);
        $output .= '$';
        $i = 0;
        do {
            $c1 = ord($input[$i++]);
            $output .= $itoa64[$c1 >> 2];
            $c1 = ($c1 & 0x03) << 4;
            if ($i >= 16) {
                $output .= $itoa64[$c1];
                break;
            }

            $c2 = ord($input[$i++]);
            $c1 |= $c2 >> 4;
            $output .= $itoa64[$c1];
            $c1 = ($c2 & 0x0f) << 2;

            $c2 = ord($input[$i++]);
            $c1 |= $c2 >> 6;
            $output .= $itoa64[$c1];
            $output .= $itoa64[$c2 & 0x3f];
        } while (1);

        return $output;
    }

    /**
     * Hash the password
     *
     * @param  string $password the password the be random hashed
     * @return string           hashed password
     */
    public function hashPassword($password)
    {
        if (!is_string($password)) {
            return null;
        }
        $random = '';

        if (CRYPT_BLOWFISH == 1 && ! $this->x_portable_hashes) {
            $random = $this->getRandomBytes(16);
            $hash =
                crypt($password, $this->gensaltBlowfish($random));
            if (strlen($hash) == 60) {
                return $hash;
            }
        }

        if (CRYPT_EXT_DES == 1 && ! $this->x_portable_hashes) {
            if (strlen($random) < 3) {
                $random = $this->getRandomBytes(3);
            }
            $hash =
                crypt($password, $this->gensaltExtended($random));
            if (strlen($hash) == 20) {
                return $hash;
            }
        }

        if (strlen($random) < 6) {
            $random = $this->getRandomBytes(6);
        }
        $hash = $this->cryptPrivate(
            $password,
            $this->gensaltPrivate($random)
        );
        if (strlen($hash) == 34) {
            return $hash;
        }

        # Returning '*' on error is safe here, but would _not_ be safe
        # in a crypt(3)-like function used _both_ for generating new
        # hashes and for validating passwords against existing hashes.

        return '*';
    }

    /**
     * Checking match password between encrypted and plain password
     *
     * @param  string $password    plain text password
     * @param  string $stored_hash hashed password
     * @return boolean              true if match
     */
    public function checkPassword($password, $stored_hash)
    {
        if (!is_string($password) || !is_string($stored_hash)) {
            return false;
        }
        $hash = $this->cryptPrivate($password, $stored_hash);
        if ($hash[0] == '*') {
            $hash = crypt($password, $stored_hash);
        }

        return $hash == $stored_hash;
    }
}
