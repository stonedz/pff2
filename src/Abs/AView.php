<?php

namespace pff\Abs;
use pff\Core\ServiceContainer;
use pff\Iface\IRenderable;

/**
 * Every view must implement this abstract class
 *
 * @author paolo.fagni<at>gmail.com
 */
abstract class AView implements IRenderable {

    /**
     * @var \pff\App
     */
    private $_app;

    /**
     * @var string The template file
     */
    protected $_templateFile;

    /**
     * @var string Path to the public folder
     */
    protected $_publicFolder;

    /**
     * @var string Path to public css path
     */
    protected $_cssFolder;

    /**
     * @var string Path to public img path
     */
    protected $_imgFolder;

    /**
     * @var string Path to the javascript folder
     */
    protected $_jsFolder;

    public function __construct($templateName) {
        $this->_app          = ServiceContainer::get('app');
        $this->_templateFile = $templateName;
        $this->_publicFolder = $this->_app->getExternalPath() . 'app' . DS . 'public' . DS;
        $this->_cssFolder    = $this->_app->getExternalPath() . 'app' . DS . 'public' . DS . 'css' . DS;
        $this->_imgFolder    = $this->_app->getExternalPath() . 'app' . DS . 'public' . DS . 'img' . DS;
        $this->_jsFolder     = $this->_app->getExternalPath() . 'app' . DS . 'public' . DS . 'js' . DS;
        $this->_filesFolder  = $this->_app->getExternalPath() . 'app' . DS . 'public' . DS . 'files' . DS;
        $this->_vendorFolder = $this->_app->getExternalPath() . 'app' . DS . 'vendor' . DS;

        $this->updatePaths();
    }

    public function updatePaths() {
        $this->set('pff_path_public', $this->_publicFolder);
        $this->set('pff_path_css', $this->_cssFolder);
        $this->set('pff_path_img', $this->_imgFolder);
        $this->set('pff_path_js', $this->_jsFolder);
        $this->set('pff_path_files', $this->_filesFolder);
        $this->set('pff_path_vendor', $this->_vendorFolder);
        $this->set('pff_root_ext', $this->_app->getExternalPath());
    }

    /**
     * @param $controller
     * @param string $action
     * @param array $params
     */
    public function renderAction($controller, $action = 'index', $params = array()) {
        $controllerClass = '\\pff\\controllers\\'.ucfirst($controller) . '_Controller';
        $tmpController   = new $controllerClass($controller, $this->_app, $action, $params);
        $tmpController->$action();
    }

    /**
     * @return string
     */
    public function getTemplateFile() {
        return $this->_templateFile;
    }

    /**
     * @return \pff\App
     */
    public function getApp() {
        return $this->_app;
    }

    /**
     * @param string $cssFolder
     */
    public function setCssFolder($cssFolder) {
        $this->_cssFolder = $cssFolder;
    }

    /**
     * @return string
     */
    public function getCssFolder() {
        return $this->_cssFolder;
    }

    /**
     * @param string $imgFolder
     */
    public function setImgFolder($imgFolder) {
        $this->_imgFolder = $imgFolder;
    }

    /**
     * @return string
     */
    public function getImgFolder() {
        return $this->_imgFolder;
    }

    /**
     * @param string $jsFolder
     */
    public function setJsFolder($jsFolder) {
        $this->_jsFolder = $jsFolder;
    }

    /**
     * @return string
     */
    public function getJsFolder() {
        return $this->_jsFolder;
    }

    /**
     * @param string $publicFolder
     */
    public function setPublicFolder($publicFolder) {
        $this->_publicFolder = $publicFolder;
    }

    /**
     * @return string
     */
    public function getPublicFolder() {
        return $this->_publicFolder;
    }

    public function getFilesFolder() {
        return $this->_filesFolder;
    }

    public function setFilesFolder($filesFolder) {
        $this->_filesFolder = $filesFolder;
    }

    public function addContent(AView $v) {
        return null;
    }

    public function content($index = 0) {
        return null;
    }
}
