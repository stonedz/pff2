<?php

declare(strict_types=1);

namespace pff\Core;

use pff\Abs\AView;
use pff\App;
use pff\Exception\ViewException;

/**
 * View that uses plain php files as template.
 *
 * Templates access data via $this->get('key') instead of bare variables.
 * Use $this->e('key') for auto-escaped HTML output.
 *
 * @author paolo.fagni<at>gmail.com
 */
class ViewPHP extends AView
{
    /**
     * @var array Contains the data to be used in the template file
     */
    private array $_data = [];

    public function __construct(string $templateName)
    {
        if (!str_starts_with($templateName, '/')) {
            $templatePath = ROOT . DS . 'app' . DS . 'views' . DS . $templateName;
        } else {
            $templatePath = $templateName;
        }
        if (!file_exists($templatePath)) {
            throw new ViewException('Template file ' . $templatePath . ' does not exist');
        }
        parent::__construct($templateName);
    }

    public function set(string $name, mixed $value): void
    {
        $this->_data[$name] = $value;
    }

    /**
     * Gets a template variable by name.
     *
     * Use this in templates instead of bare variables: $this->get('name')
     *
     * @param string $name Variable name
     * @param mixed $default Default value if variable is not set
     * @return mixed
     */
    public function get(string $name, mixed $default = null): mixed
    {
        return $this->_data[$name] ?? $default;
    }

    /**
     * Checks if a template variable is set.
     *
     * @param string $name Variable name
     * @return bool
     */
    public function has(string $name): bool
    {
        return array_key_exists($name, $this->_data);
    }

    /**
     * Returns all template data.
     *
     * @return array
     */
    public function getData(): array
    {
        return $this->_data;
    }

    public function render(): void
    {
        $templatePath = $this->getTemplatePath();
        if (!file_exists($templatePath)) {
            throw new ViewException('Template file ' . $templatePath . ' does not exist');
        }
        include($templatePath);
    }

    public function renderHtml(): string
    {
        $templatePath = $this->getTemplatePath();
        if (!file_exists($templatePath)) {
            throw new ViewException('Template file ' . $templatePath . ' does not exist');
        }

        include($templatePath);
        $output = ob_get_contents();
        ob_clean();
        return $output;
    }

    /**
     * Callback method to sanitize HTML output
     *
     * @param string $output HTML output string
     * @return string
     */
    public function preView(string $output): string
    {
        /** @var $purifierConfig \HTMLPurifier_Config */
        $purifierConfig = \HTMLPurifier_Config::createDefault();
        $purifierConfig->set('Core.Encoding', 'UTF-8');
        $purifierConfig->set('HTML.TidyLevel', 'medium');

        /** @var \HTMLPurifier_Config $purifierConfig */
        $purifier = new \HTMLPurifier($purifierConfig);
        $output = $purifier->purify($output);

        return $output;
    }

    /**
     * @return string
     */
    private function getTemplatePath(): string
    {
        if (!str_starts_with($this->_templateFile, '/')) {
            $templatePath = ROOT . DS . 'app' . DS . 'views' . DS . $this->_templateFile;
            return $templatePath;
        } else {
            $templatePath = $this->_templateFile;
            return $templatePath;
        }
    }
}
