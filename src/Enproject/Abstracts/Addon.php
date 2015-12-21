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

use Aufa\Enproject\Interfaces\InterfaceAddon;

/**
 * Abstract Addon to use addon implementation
 *     Addon hookable System building hookable site system
 * @uses  Aufa\ErSysDucation\Interface\InterfaceAddon
 */
abstract class Addon implements InterfaceAddon
{
    /* --------------------------------------------------------------------------------*
     |                                Class Properties                                 |
     |---------------------------------------------------------------------------------|
     */

    /**
     * Addon author
     * @var string
     */
    protected $addon_author       = '';

    /**
     * Addon author URL
     * @var string
     */
    protected $addon_author_uri   = '';

    /**
     * Addon Description
     * @var string
     */
    protected $addon_description  = '';

    /**
     * Addon Name
     * @var string
     */
    protected $addon_name         = '';

    /**
     * Addon License Type
     * @var string
     */
    protected $addon_license      = '';

    /**
     * Addon URL
     * @var string
     */
    protected $addon_uri          = '';

    /**
     * Addon Version
     * @var string
     */
    protected $addon_version      = '';

    /**
     * Addon Latest Version information
     * @var array
     */
    protected $addon_latest_version = array(
        'version'   => '',
        'uri'       => '',
        'changelog' => '',
        'downloadable' => false,
    );

    /* --------------------------------------------------------------------------------*
     |                                  Class Method                                   |
     |---------------------------------------------------------------------------------|
     */
    /**
     * PHP5 constructor final prevent direct access
     *     that hook call init
     * @final __constructor();
     */
    final public function __construct()
    {
        return $this;
    }

    /**
     * Get called / children class called into this abstract
     * @access public called instantly
     * @return string children class
     */
    final public function addonGetClassName()
    {
        return get_class($this);
    }

    /**
     * Get Addon Author
     * @access protected for internal information use only
     * @return string author
     */
    final public function addonGetAuthor()
    {
        is_string($this->addon_author) ||
           $this->addon_author = '';

        return $this->addon_author;
    }

    /**
     * Get Addon Author
     * @access protected for internal information use only
     * @return string author
     */
    final public function addonGetAuthorUri()
    {
        is_string($this->addon_author_uri) ||
            $this->addon_author_uri = '';

        return $this->addon_author_uri;
    }

    /**
     * Get Addon Author
     * @access protected for internal information use only
     * @return string description
     */
    final public function addonGetDescription()
    {
        is_string($this->addon_description) ||
            $this->addon_description = '';

        return $this->addon_description;
    }

    /**
     * Get Addon License
     * @access protected for internal information use only
     * @return string version
     */
    final public function addonGetLicense()
    {
        return $this->addon_license;
    }

    /**
     * Get Addon Name
     * @access protected for internal information use only
     * @return string addon Name
     */
    final public function addonGetName()
    {
        return $this->addon_name;
    }

    /**
     * Get Addon URL
     * @access protected for internal information use only
     * @return string addon URL
     */
    final public function addonGetUri()
    {
        is_string($this->addon_uri) ||
            $this->addon_uri = '';

        return $this->addon_uri;
    }

    /**
     * Get Addon Version
     * @access protected for internal information use only
     * @return string version
     */
    final public function addonGetVersion()
    {
        return $this->addon_version;
    }

    /**
     * Get addon version is up to date
     * @access protected for internal information use only
     * @return boolean true if up to date
     */
    final public function addonGetVersionIsUptodate()
    {
        return version_compare($this->latestVersion(), $this->addonGetVersion(), '<=');
    }

    /**
     * Initialize latest Version Update Array
     * @access protected for internal information use only
     * @return array latest version details
     */
    final public function addonInitializeLatestVersion($force = false)
    {
        $default =  array(
            'version'   => '',
            'uri'       => '',
            'changelog' => '',
            'downloadable' => false,
        );
        if (! is_array($this->addon_latest_version)) {
            $this->addon_latest_version = $default;
        } else {
            // cahce force
            static $called;
            if ($called && !$force) {
                return $this->addon_latest_version;
            }

            $called = true;
            $this->addon_latest_version = array_merge(
                $default,
                $this->addon_latest_version
            );
            // reset version
            !in_array(gettype($this->addon_latest_version['version']), array('string', 'integer')) &&
                $this->addon_latest_version['version'] = '';
            // reset changelog
            !is_string($this->addon_latest_version['changelog']) &&
                $this->addon_latest_version['changelog'] = '';
            // reset changelog
            (!is_string($this->addon_latest_version['uri'])
                || ! filter_var($this->addon_latest_version['uri'], FILTER_VALIDATE_URL)
                ) && $this->addon_latest_version['uri'] = '';
            // reset downloadable
            ! $this->addon_latest_version['uri'] &&
                $this->addon_latest_version['downloadable'] = false;
        }

        return $this->addon_latest_version;
    }

    /**
     * Overidden Function latest Version
     * @return string the latest Version
     */
    public function latestVersion($force = false)
    {
        $version = $this->addonInitializeLatestVersion($force);
        if (!empty($version['version'])) {
            return $version['version'];
        }

        return $this->addonGetVersion();
    }

    /* --------------------------------------------------------------------------------*
     |                                   Overloading                                   |
     |---------------------------------------------------------------------------------|
     */

    /**
     * Init running class hooks
     */
    public function run()
    {
        return false;
    }

    /**
     * Magic Method For Backwards Compatibility
     * @return string
     */
    public function __toString()
    {
        return $this->addongetClassName();
    }
}
