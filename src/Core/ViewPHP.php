<?php

namespace pff\Core;
use pff\Abs\AView;
use pff\Exception\ViewException;

/**
 * View that uses plain php files as template.
 *
 * @author paolo.fagni<at>gmail.com
 */
class ViewPHP extends AView {

    /**
     * @var array Contains the data to be used in the template file
     */
    private $_data;

    public function __construct($templateName, \pff\App $app)
    {
        if (substr($templateName, 0, 1) != '/'){
            $templatePath = ROOT . DS . 'app' . DS . 'views' . DS . $templateName;
        }
        else {
            $templatePath = $templateName;
        }
        if (!file_exists($templatePath)) {
            throw new ViewException('Template file ' . $templatePath . ' does not exist');
        }
        parent::__construct($templateName, $app);
    }

    public function set($name, $value) {
        $this->_data[$name] = $value;
    }

    public function render() {
        if (substr($this->_templateFile, 0, 1) != '/'){
            $templatePath = ROOT . DS . 'app' . DS . 'views' . DS . $this->_templateFile;
        }
        else {
            $templatePath = $this->_templateFile;
        }
        if (!file_exists($templatePath)) {
            throw new ViewException('Template file ' . $templatePath . ' does not exist');
        }
        if (is_array($this->_data)) {
            extract($this->_data); // Extract set data to scope vars
        }
        include ($templatePath);
    }

    public function renderHtml() {
        if (substr($this->_templateFile, 0, 1) != '/'){
            $templatePath = ROOT . DS . 'app' . DS . 'views' . DS . $this->_templateFile;
        }
        else {
            $templatePath = $this->_templateFile;
        }
        if (!file_exists($templatePath)) {
            throw new ViewException('Template file ' . $templatePath . ' does not exist');
        }
        if (is_array($this->_data)) {
            extract($this->_data); // Extract set data to scope vars
        }

        include ($templatePath);
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
    public function preView($output) {
        /** @var $purifierConfig \HTMLPurifier_Config */
        $purifierConfig = \HTMLPurifier_Config::createDefault();
        $purifierConfig->set('Core.Encoding', 'UTF-8');
        $purifierConfig->set('HTML.TidyLevel', 'medium');

        /** @var \HTMLPurifier_Config $purifierConfig */
        $purifier = new \HTMLPurifier($purifierConfig);
        $output   = $purifier->purify($output);

        return $output;
    }
}
