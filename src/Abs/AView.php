<?php

declare(strict_types=1);

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
    private readonly \pff\App $_app;

    protected string $_publicFolder;

    protected string $_cssFolder;

    protected string $_imgFolder;

    protected string $_jsFolder;

    protected string $_filesFolder;

    protected string $_vendorFolder;


    public function __construct(protected string $_templateFile)
    {
        $this->_app = ServiceContainer::get('app');
        $this->_publicFolder = $this->_app->getExternalPath() . 'app' . DS . 'public' . DS;
        $this->_cssFolder = $this->_app->getExternalPath() . 'app' . DS . 'public' . DS . 'css' . DS;
        $this->_imgFolder = $this->_app->getExternalPath() . 'app' . DS . 'public' . DS . 'img' . DS;
        $this->_jsFolder = $this->_app->getExternalPath() . 'app' . DS . 'public' . DS . 'js' . DS;
        $this->_filesFolder = $this->_app->getExternalPath() . 'app' . DS . 'public' . DS . 'files' . DS;
        $this->_vendorFolder = $this->_app->getExternalPath() . 'app' . DS . 'vendor' . DS;

        $this->updatePaths();
    }

    public function updatePaths(): void
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
    public function renderAction(string $controller, string $action = 'index', array $params = []): void
    {
        $controllerClass = '\\pff\\controllers\\' . ucfirst($controller) . '_Controller';
        $tmpController = new $controllerClass($controller, $this->_app, $action, $params);
        $tmpController->$action();
        $tmpController->setIsRenderAction(true);
    }

    public function getTemplateFile(): string
    {
        return $this->_templateFile;
    }

    public function getApp(): \pff\App
    {
        return $this->_app;
    }

    public function setCssFolder(string $cssFolder): void
    {
        $this->_cssFolder = $cssFolder;
    }

    public function getCssFolder(): string
    {
        return $this->_cssFolder;
    }

    public function setImgFolder(string $imgFolder): void
    {
        $this->_imgFolder = $imgFolder;
    }

    public function getImgFolder(): string
    {
        return $this->_imgFolder;
    }

    public function setJsFolder(string $jsFolder): void
    {
        $this->_jsFolder = $jsFolder;
    }

    public function getJsFolder(): string
    {
        return $this->_jsFolder;
    }

    public function setPublicFolder(string $publicFolder): void
    {
        $this->_publicFolder = $publicFolder;
    }

    public function getPublicFolder(): string
    {
        return $this->_publicFolder;
    }

    public function getFilesFolder(): string
    {
        return $this->_filesFolder;
    }

    public function setFilesFolder(string $filesFolder): void
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

    public function addContent(AView $v): void
    {
    }

    public function content(int $index = 0): void
    {
    }
}
