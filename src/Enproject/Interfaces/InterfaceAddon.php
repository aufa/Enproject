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

namespace Aufa\Enproject\Interfaces;

/**
 * Interface addon to build Hook abstract Addon
 *     The addon hookable class
 * @see  \Aufa\Enproject\Abstracts\AbstractAddon
 */
interface InterfaceAddon extends EmptyInterface
{
    public function __construct();
    public function addonGetClassName();
    public function addonGetAuthor();
    public function addonGetAuthorUri();
    public function addonGetDescription();
    public function addonGetLicense();
    public function addonGetName();
    public function addonGetVersion();
    public function addonGetVersionIsUptodate();
    public function addonInitializeLatestVersion();
    public function latestVersion();
    public function run();
}
