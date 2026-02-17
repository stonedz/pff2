<?php

namespace pff\Abs;

use pff\Core\ModuleManager;
use pff\Core\ServiceContainer;
use pff\Iface\IRenderable;

/**
 * Every view must implement this abstract class
 *
 * @author paolo.fagni<at>gmail.com
 */
abstract class AView implements IRenderable
{
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

    /**
     * @var string Path to the files folder
     */
    protected $_filesFolder;

    /**
     * @var string Path to the composer's vendor folder
     */
    protected $_vendorFolder;


    public function __construct($templateName)
    {
        $this->_app = ServiceContainer::get('app');
        $this->_templateFile = $templateName;
        $this->_publicFolder = $this->_app->getExternalPath() . 'app' . DS . 'public' . DS;
        $this->_cssFolder = $this->_app->getExternalPath() . 'app' . DS . 'public' . DS . 'css' . DS;
        $this->_imgFolder = $this->_app->getExternalPath() . 'app' . DS . 'public' . DS . 'img' . DS;
        $this->_jsFolder = $this->_app->getExternalPath() . 'app' . DS . 'public' . DS . 'js' . DS;
        $this->_filesFolder = $this->_app->getExternalPath() . 'app' . DS . 'public' . DS . 'files' . DS;
        $this->_vendorFolder = $this->_app->getExternalPath() . 'app' . DS . 'vendor' . DS;

        $this->updatePaths();
    }

    public function updatePaths()
    {
        $this->set('pff_path_public', $this->_publicFolder);
        $this->set('pff_path_css', $this->_cssFolder);
        $this->set('pff_path_img', $this->_imgFolder);
        $this->set('pff_path_js', $this->_jsFolder);
        if (ModuleManager::isLoaded('pff2-s3')) {
            $s3 = ModuleManager::loadModule('pff2-s3');
            $this->set('pff_path_files', $s3->getCloudfrontUrl() ?: $this->_filesFolder);
        } else {
            $this->set('pff_path_files', $this->_filesFolder);
        }
        $this->set('pff_path_vendor', $this->_vendorFolder);
        $this->set('pff_root_ext', $this->_app->getExternalPath());
    }

    /**
     * @param $controller
     * @param string $action
     * @param array $params
     */
    public function renderAction($controller, $action = 'index', $params = [])
    {
        $controllerClass = '\\pff\\controllers\\' . ucfirst($controller) . '_Controller';
        $tmpController = new $controllerClass($controller, $this->_app, $action, $params);
        $tmpController->$action();
        $tmpController->setIsRenderAction(true);
    }

    /**
     * @return string
     */
    public function getTemplateFile()
    {
        return $this->_templateFile;
    }

    /**
     * @return \pff\App
     */
    public function getApp()
    {
        return $this->_app;
    }

    /**
     * @param string $cssFolder
     */
    public function setCssFolder($cssFolder)
    {
        $this->_cssFolder = $cssFolder;
    }

    /**
     * @return string
     */
    public function getCssFolder()
    {
        return $this->_cssFolder;
    }

    /**
     * @param string $imgFolder
     */
    public function setImgFolder($imgFolder)
    {
        $this->_imgFolder = $imgFolder;
    }

    /**
     * @return string
     */
    public function getImgFolder()
    {
        return $this->_imgFolder;
    }

    /**
     * @param string $jsFolder
     */
    public function setJsFolder($jsFolder)
    {
        $this->_jsFolder = $jsFolder;
    }

    /**
     * @return string
     */
    public function getJsFolder()
    {
        return $this->_jsFolder;
    }

    /**
     * @param string $publicFolder
     */
    public function setPublicFolder($publicFolder)
    {
        $this->_publicFolder = $publicFolder;
    }

    /**
     * @return string
     */
    public function getPublicFolder()
    {
        return $this->_publicFolder;
    }

    public function getFilesFolder()
    {
        return $this->_filesFolder;
    }

    public function setFilesFolder($filesFolder)
    {
        $this->_filesFolder = $filesFolder;
    }

    /**
     * Escape a value for safe output in the given context.
     *
     * Contexts: 'html' (default), 'attr', 'js', 'url'.
     *
     * Usage in PHP templates:
     *   <?= $this->e($this->get('name')) ?>
     *
     * @param mixed $value The value to escape
     * @param string $context The escaping context
     * @return string
     */
    public function e(mixed $value, string $context = 'html'): string
    {
        $value = (string) $value;

        return match ($context) {
            'html', 'attr' => htmlspecialchars($value, ENT_QUOTES | ENT_HTML5 | ENT_SUBSTITUTE, 'UTF-8'),
            'js' => json_encode($value, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_THROW_ON_ERROR),
            'url' => rawurlencode($value),
            default => htmlspecialchars($value, ENT_QUOTES | ENT_HTML5 | ENT_SUBSTITUTE, 'UTF-8'),
        };
    }

    /**
     * Alias for e() — escape a value for safe output.
     *
     * @param mixed $value The value to escape
     * @param string $context The escaping context
     * @return string
     */
    public function escape(mixed $value, string $context = 'html'): string
    {
        return $this->e($value, $context);
    }

    public function addContent(AView $v)
    {
        return null;
    }

    public function content($index = 0)
    {
        return null;
    }
}
