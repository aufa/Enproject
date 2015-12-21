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

namespace Aufa\Enproject\Http;

use Aufa\Enproject\Collector\Collector;

/**
 * Cookie Request
 */
class Cookie extends Collector
{
    /**
     * ServerCollector
     * @var object
     */
    protected static $Collector;

    /**
     * Constructor
     *
     * @override (doesn't call our parent)
     * @param array $headers        The headers of this collection
     * @param int $normalization    The header key normalization technique/style to use
     */
    public function __construct($items = array())
    {
        foreach ($items as $key => $value) {
            $this->set($key, $value);
        }
    }

    /**
     * Set cookie
     *
     * The second argument may be a single scalar value, in which case
     * it will be merged with the default settings and considered the `value`
     * of the merged result.
     *
     * The second argument may also be an array containing any or all of
     * the keys shown in the default settings above. This array will be
     * merged with the defaults shown above.
     *
     * @param string $key   Cookie name
     * @param mixed  $value Cookie settings
     */
    public function set($key, $value)
    {
        $defaults = array(
            'value' => '',
            'domain' => null,
            'path' => null,
            'expires' => null,
            'secure' => false,
            'httponly' => false,
            'encrypted' => null, // force encrypted
        );

        if (is_array($value)) {
            if (isset($value['encrypted']) && !is_bool($value['encrypted'])) {
                $value['encrypted'] = ! $value['encrypted'] ? false : true;
            }

            $cookieSettings = array_replace($defaults, $value);
        } else {
            $cookieSettings = array_replace($defaults, array('value' => $value));
        }

        parent::set($key, $cookieSettings);
    }
}
