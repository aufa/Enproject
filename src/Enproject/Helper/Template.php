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
use Aufa\Enproject\Http\Request;
use Aufa\Enproject\Config;

/**
 * Handling Template scanning and set active and get info for some template existences
 */
class Template extends Singleton
{
    /* --------------------------------------------------------------------------------*
     |                              Class Properties                                   |
     |---------------------------------------------------------------------------------|
     */
    
    /**
     * Default template
     * @var string
     */
    public $x_default_template = 'Default';
    
    /**
     * File to read Comment as Main template file
     * @var string
     */
    public $x_file_to_read     = 'templates.php';

    /**
     * File that must be exists on template
     * eg : header.php, footer.php
     * @var array
     */
    public $x_mustbe_exist = array();

    /**
     * set Default View file
     * @var string
     */
    public $x_default_file = 'default.php';

    /**
     * set 404 not found file
     * @var string
     */
    public $x_404_file = '404.php';

    /**
     * Set 500 error File
     * @var string
     */
    public $x_500_file = '500.php';

    /**
     * Set 500 error File
     * @var string
     */
    public $x_blocked_file = 'blocked.php';

    /**
     * Regex Headers to parse Information of template
     * @var array
     */
    public $x_headers = array(
        'Name'       => 'package',  // @package comment doc as Template Name
        'Author'     => 'author',   // @author as author of template
        'AuthorUri'  => 'authorUri',// @authorUri as author URL of template
        'Url'        => 'link',     // @link as link of template
        'Version'    => 'version',  // @version as version of current template
        'License'    => 'license',  // @license as license information
        'Description'=> 'description', // @description as description of template
    );

    /**
     * The Base Name of Active template
     * @var string
     */
    protected $x_active_template = null;

    /**
     * List available template
     * @var array
     */
    protected $x_list_templates  = array();

    /**
     * Templates Directory
     * @var string
     */
    public $templates_directory = null;

    /**
     * Bas Url or Template
     * @var null
     */
    protected $templates_directory_url = null;

    /**
     * PHP5 Constructor
     */
    public function __construct()
    {
        parent::__construct();
    }

    public static function init()
    {
        return static::singleton()->standardInit();
    }

    /**
     * Init , template
     *  becarefull, if template has been called once it will be no affected to this init
     * @return object current class
     */
    public function standardInit()
    {
        /**
         * If one has been called no call anymore!
         * @var boolean static cached
         */
        static $has_called;

        // prevent multiple called
        if ($has_called) {
            return $this;
        }

        $has_called = true;
        $has_set    = false;
        if (!$this->templates_directory || ! is_string($this->templates_directory)) {
            $template = Config::Get('template_directory', null);
            if (! $template && $template = realpath('templates')) {
                $template = 'templates';
            } elseif ($template && is_string($template)) {
                $has_set  = true;
                $template = $template ? realpath(trim($template)) : null;
            }

            $this->templates_directory = ! $template ? null : $template;
            unset($template);
        }

        /**
         * Checking
         */
        if (! $this->templates_directory
            || is_string($this->templates_directory) &&
            trim($this->templates_directory) !== '' && ! Internal::isDir($this->templates_directory)
        ) {
            if ($has_set || is_string($this->templates_directory)) {
                trigger_error(
                    'Templates directory does not exists!',
                    E_USER_ERROR
                );
            }
            return $this;
        }

        /**
         * Templates Directory Replace if it can
         * That we just try
         */
        Config::replace('templates_directory', $this->templates_directory);

        if (!$this->x_default_template || !is_string($this->x_default_template) || !trim($this->x_default_template)) {
            $this->x_default_template = 'Default';
        }

        if (!is_string($this->x_file_to_read) || !trim($this->x_file_to_read)) {
            $this->x_file_to_read = 'templates.php';
        }

        /**
         * Trimming empty right and left
         * @var string
         */
        $this->x_default_template = trim($this->x_default_template);
        $this->x_file_to_read = trim($this->x_file_to_read);

        if (!is_array($this->x_mustbe_exist)) {
            trigger_error('Invalid Templates Definition!', E_USER_ERROR);
            return $this;
        }
        if (!is_array($this->x_headers)) {
            trigger_error('Invalid Templates Headers Definition!', E_USER_ERROR);
            return $this;
        }

        // add file to read to be must be exists
        in_array($this->x_file_to_read, $this->x_mustbe_exist) || $this->x_mustbe_exist[] = $this->x_file_to_read;

        $this->templates_directory = Path::cleanPath(realpath($this->templates_directory));
        $this->buildInit();
        // set
        if (!empty($this->x_list_templates)) {
            if (!array_key_exists($this->x_default_template, $this->x_list_templates)) {
                $activeTemplate = $this->getAllTemplate();
                $this->x_default_template = key($activeTemplate);
            }

            // set active templates
            $this->setActiveTemplate($this->x_default_template);
        }

        return $this;
    }

    /* --------------------------------------------------------------------------------*
     |                           Final Private Method                                  |
     |---------------------------------------------------------------------------------|
     */

    final private function buildInit()
    {
        /**
         * If one has been called no call anymore!
         * @var boolean static cached
         */
        static $has_called;

        // prevent multiple called
        if ($has_called) {
            return $this->x_list_templates;
        }

        $has_called = true;
        $documentRoot = Path::documentRoot();
        $template_after_dir = preg_replace(
            '/^'.preg_quote($documentRoot, '/').'/',
            '',
            $this->templates_directory
        );

        $this->templates_directory_url = Request::baseUrl(rtrim($template_after_dir, '/'));
        foreach ((array) Internal::readDirList($this->templates_directory, 1) as $key => $value) {
            if (!is_file("{$this->templates_directory}/{$value}/{$this->x_file_to_read}")) {
                continue;
            }
            $this->buildTemplateDetails("{$this->templates_directory}/{$value}");
        }
        return $this->x_list_templates;
    }

    final private function buildTemplateDetails($templateDirectory)
    {
        if (!Internal::isDir($templateDirectory)) {
            return;
        }
        $basename = basename($templateDirectory);
        if (isset($this->x_list_templates[$basename])) {
            return;
        }
        $templates_directory = Path::cleanPath(dirname($templateDirectory));
        $info = array();
        foreach ((array) Internal::readDirList($templateDirectory, 1) as $key => $value) {
            if (is_file("{$templateDirectory}/{$value}")) {
                $info[] = $value;
            }
        }

        $diff = array_diff($this->x_mustbe_exist, $info);
        $this->x_list_templates[$basename] = $this->readTemplateInfo($templateDirectory);
        $this->x_list_templates[$basename]['Directory'] = $templateDirectory;
        $this->x_list_templates[$basename]['Valid'] = empty($diff);
        $this->x_list_templates[$basename]['Corrupt']  = $diff;
        $this->x_list_templates[$basename]['BaseName'] = $basename;
        unset($diff, $info);
    }

    final private function getTemplateInfo($name)
    {
        if ($name && isset($this->x_list_templates[$name])) {
            return $this->x_list_templates[$name];
        }
        $args[$name] = $this->readTemplateInfo(null);
        $args[$name]['Directory'] = null;
        $args[$name]['Valid']    = false;
        $args[$name]['Corrupt']  = array();
        $args[$name]['BaseName'] = $name;
        return $args[$name];
    }

    final private function readTemplateInfo($templateDirectory)
    {
        $file = $templateDirectory ? "{$templateDirectory}/{$this->x_file_to_read}" : null;
        $file_data = '';
        // We don't need to write to the file, so just open for reading.
        if ($file && is_file($file) && $fp = fopen($file, 'r')) {
            // Pull only the first 8kiB of the file in.
            $file_data = fread($fp, 8192);
            // PHP will close file handle, but we are good citizens.
            fclose($fp);
            // Make sure we catch CR-only line endings.
            $file_data = str_replace("\r", "\n", $file_data);
        }

        $all_headers = $this->x_headers;
        foreach ($all_headers as $field => $regex) {
            if (trim($file_data)
                && preg_match('/^[ \t\/*#@]*' . preg_quote("{$regex}", '/') . '(\:+)?(.*)$/mi', $file_data, $match)
                && $match[2]
            ) {
                $all_headers[$field] = trim(preg_replace("/\s*(?:\*\/|\?>).*/", '', $match[2]));
            } else {
                $all_headers[$field] = '';
            }
        }
        unset($file_data);
        return $all_headers;
    }

    public static function getTemplateDirectory()
    {
        return static::singleton()->templates_directory;
    }

    public function getAllTemplate()
    {
        return $this->x_list_templates;
    }

    public static function getAll()
    {
        return static::singleton()->getAllTemplate();
    }

    public function setActiveTemplate($templateName)
    {
        // check if template valid
        if (!empty($this->x_list_templates[$templateName]['Valid'])) {
            $this->x_active_template = $templateName;
        }
    }

    /**
     * Aliases static::setActiveTemplate();
     * @param string $templateName template name
     */
    public static function setActive($templateName)
    {
        return static::singleton()->setActiveTemplate($templateName);
    }

    public function isActiveTemplate($templateName)
    {
        return ($this->x_active_template == $templateName);
    }

    public static function isActive($templateName)
    {
        return static::singleton()->isActiveTemplate($templateName);
    }

    public function getActiveTemplate()
    {
        return $this->x_active_template;
    }

    public static function getActive()
    {
        return static::singleton()->getActiveTemplate();
    }

    public function getActiveTemplateDetails()
    {
        return $this->getTemplateInfo($this->x_active_template);
    }

    public function getActiveTemplateDetail()
    {
        return $this->getActiveTemplateDetails();
    }

    public static function getActiveDetails()
    {
        return static::singleton()->getActiveTemplateDetails();
    }

    public static function getActiveDetail()
    {
        return static::singleton()->getActiveTemplateDetails();
    }

    public function getActiveTemplateDirectory()
    {
        $template = $this->getActiveTemplateDetails();
        return $template['Directory'];
    }

    public static function getActiveDirectory()
    {
        return static::singleton()->getActiveTemplateDirectory();
    }

    public function getActiveTemplatePathFileFor($file)
    {
        if (is_string($file)) {
            $template_dir = $this->getActiveTemplateDirectory();
            $file = Path::cleanPath($file);
            $file = trim(preg_replace('/^'.preg_quote($template_dir, '/').'/', '', $file), '/');
            if (is_file("{$template_dir}/{$file}")) {
                return "{$template_dir}/{$file}";
            }
        }
    }

    public static function getActivePathFileFor($file)
    {
        return static::singleton()->getPathFileFor($file);
    }

    public function setActiveTemplateDirectory($directory)
    {
        if (Internal::isDir($directory)) {
            $active = $this->getActiveTemplate();
            $this->x_list_templates[$active]['Directory'] = Path::cleanSlashed($directory);
            return true;
        }
        return false;
    }

    public static function setActiveDirectory($directory)
    {
        return static::singleton()->setActiveTemplateDirectory($directory);
    }

    /**
     * Magic Method __get to prevent error
     * @param  string $name Property
     */
    public function __get($name)
    {
        if (!property_exists($this, $name)) {
            return null;
        }
    }
}
