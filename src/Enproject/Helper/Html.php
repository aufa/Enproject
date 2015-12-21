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

/**
 * CLass HTML Output Helper
 */
class Html
{
    /**
     * Cached Property
     * @var array
     */
    protected static $x_property = array();

    /**
     * Html returning data
     *
     * @param  string $title    the title
     * @param  string $message  html content
     * @param  array  $property property
     * @return string           html output
     */
    public static function create($title = null, $message = null, $property = array())
    {
        if (is_array($property)) {
            self::setProperty($property);
        }
        if (is_string($title)) {
            self::setProperty(array('title' => $title));
        }
        if (is_string($message)) {
            self::setProperty(array('content' => $message));
        }

        return static::build();
    }

    /**
     * Set Property
     *
     * @param array|string  $array property or string as key name
     * @param string        $value property value
     */
    public static function setProperty($array = array(), $value = null)
    {
        if (!is_array($array)) {
            if (!is_string($array) || ! $value) {
                $array = array();
            } else {
                $array = array($array => $value);
            }
        }
        $default = array(
            'title'   => '',
            'content' => '',
        );
        $default = array_merge($default, self::$x_property);
        $array  = array_merge($default, $array);
        is_array($array['content']) || is_object($array['content'])
            && $array['content'] = '<h2>There was an error</h2>';
        is_array($array['title']) || is_object($array['title'])
            && $array['title'] = 'Error';
        // entities title
        $array['title'] = trim($array['title']);
        self::$x_property = $array;
    }

    /**
     * Build HTML output
     *
     * @return string
     */
    public static function build()
    {
        self::setProperty(); // init
        $array = self::$x_property;
        // sanitize
        $title  = Filter::multibyteEntities($array['title']);
        $html   = array();
        $html[] = "<!DOCTYPE html>";
        $html[] = "<html>";
        $html[] = "<head>";
        $html[] = "<meta charset=\"UTF-8\">";
        $html[] = "<title>{$title}</title>";
        if (isset($array['style']) && is_string($array['style']) && trim($array['style'])) {
            $html[] = "<style type=\"text/css\">";
            $html[] = rtrim($array['style']);
            $html[] = "</style>";
        }
        if (isset($array['header'])) {
            if (is_array($array['header'])) {
                foreach ($array['header'] as $key => $value) {
                    if (is_string($value)) {
                        $html[] = $value;
                    }
                }
            } elseif (is_string($array['header'])) {
                $html[] = $array['header'];
            }
        }
        $html[] = "</head>";
        $html[] = "<body>";
        $html[] = "<div class=\"wrap\">";
        $html[] = trim(Filter::forceBalanceTags(Filter::multibyteEntities($array['content'], false)));
        $html[] = "</div>";
        if (isset($array['footer'])) {
            if (is_array($array['footer'])) {
                foreach ($array['footer'] as $key => $value) {
                    if (is_string($value)) {
                        $html[] = $value;
                    }
                }
            } elseif (is_string($array['footer'])) {
                $html[] = $array['footer'];
            }
        }
        $html[] = "</body>";
        $html[] = "</html>";
        return implode("\n", $html);
    }
}
