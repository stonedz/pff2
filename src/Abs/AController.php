<?php

declare(strict_types=1);

namespace pff\Abs;

use Doctrine\ORM\EntityManager;
use pff\App;
use pff\Core\HelperManager;
use pff\Core\ModuleManager;
use pff\Core\Outputs\HTMLOut;
use pff\Core\ServiceContainer;
use pff\Exception\PffException;
use pff\Exception\ViewException;
use pff\Iface\IOutputs;
use pff\Traits\ControllerTrait;
use pff\Iface\IController;

/**
 * Every controller must implement this abstract class
 *
 * @author paolo.fagni<at>gmail.com
 */
abstract class AController implements IController
{
    use ControllerTrait;

    /**
     * @var AView[]
     */
    protected array $_view;

    protected \pff\Config $_config;

    public ?\Doctrine\ORM\EntityManager $_em = null;

    /**
     * Reference to app's module manager
     *
     * Used to access loaded modules or load new modules
     */
    protected ModuleManager $_moduleManager;

    protected HelperManager $_helperManager;

    /**
     * Contains the registered beforeFilters
     *
     * @var array<string, array<callable>>
     */
    protected array $_beforeFilters = [];

    /**
     * Contains the registered afterFilters
     *
     * @var array<string, array<callable>>
     */
    protected array $_afterFilters = [];

    /**
     * May contain the default layout (used by main_layout module for example)
     */
    protected ?AView $_layout = null;

    protected IOutputs $_output;

    protected bool $_isRenderAction = false;

    /**
     * Creates a controller
     *
     * @param string $_controllerName The controller's name (used to load correct model)
     * @param \pff\App $_app
     * @param string $_action Action to perform
     * @param array $_params An array with parameters passed to the action
     * @internal param \pff\Config $cfg App configuration
     */
    public function __construct(
        protected string $_controllerName,
        protected ?\pff\App $_app,
        protected string $_action = 'index', /**
              * Array of parameters passed to the specified action
              */
        protected array $_params = []
    ) {
        $this->_config = $this->_app->getConfig();
        $this->_moduleManager = $this->_app->getModuleManager();
        $this->_helperManager = $this->_app->getHelperManager();
        $this->_layout = null;
        $this->_view = [];

        if ($this->_config->getConfigData('orm')) {
            $this->initORM();
        } else {
            $this->_em = null;
        }

        // Set default output to HTML
        $this->_output = new HTMLOut();

        $this->initController();
    }

    /**
     * Override this method if you want to init your controller
     *
     * @return bool
     */
    public function initController(): bool
    {
        return true;
    }

    /**
     * Initializes Doctrine entity manager and wires APCu-backed metadata/query caches when available.
     */
    private function initORM(): void
    {
        $this->_em = ServiceContainer::get('dm');

        // Wire APCu-backed metadata and query caches (PSR-6) when available
        if ($this->_em !== null
            && function_exists('apcu_fetch')
            && class_exists('\Symfony\Component\Cache\Adapter\ApcuAdapter')
        ) {
            $namespace = (string) $this->_config->getConfigData('app_name') . '_doctrine';
            $pool = new \Symfony\Component\Cache\Adapter\ApcuAdapter($namespace);
            $this->_em->getConfiguration()->setMetadataCache($pool);
            $this->_em->getConfiguration()->setQueryCache($pool);
        }
    }

    /**
     * Method executed before the action
     */
    public function beforeAction(): void
    {
    }

    /**
     * Method executed after the action
     */
    public function afterAction(): void
    {
    }

    /**
     * Adds a view
     */
    public function addView(AView $view): void
    {
        $this->_view[] = $view;
    }

    /**
     * Adds a view at the top of the stack
     */
    public function addViewPre(AView $view): void
    {
        array_unshift($this->_view, $view);
    }

    /**
     * Called before the controller is deleted.
     *
     * The view's render method is called for each view registered.
     *
     * @throws ViewException
     */
    public function __destruct()
    {
        if (!isset($this->_output) || !isset($this->_app)) {
            return;
        }

        $this->_output->outputHeader();

        if (!$this->_isRenderAction) {
            if (isset($this->_view)) {
                if (is_array($this->_view)) {
                    $this->_app->getHookManager()->runBeforeView(['controller' => $this]);
                    foreach ($this->_view as $view) {
                        $view->render();
                    }
                    $this->_app->getHookManager()->runAfterView();
                } elseif (is_a($this->_view, '\\pff\\AView')) {
                    $this->_app->getHookManager()->runBeforeView(['controller' => $this]);
                    $this->_view->render();
                    $this->_app->getHookManager()->runAfterView();
                } else {
                    throw new ViewException("The specified View is not valid.");
                }
            }
        } else {
            if (isset($this->_view)) {
                if (is_array($this->_view)) {
                    foreach ($this->_view as $view) {
                        $this->_app->getHookManager()->runBeforeView(['controller' => $this]);
                        $view->render();
                    }
                } elseif (is_a($this->_view, '\\pff\\AView')) {
                    $this->_app->getHookManager()->runBeforeView(['controller' => $this]);
                    $this->_view->render();
                } else {
                    throw new ViewException("The specified View is not valid.");
                }
            }
        }
    }

    /**
     * @return string
     */
    public function getControllerName(): string
    {
        return $this->_controllerName;
    }

    public function getAction(): string
    {
        return $this->_action;
    }

    public function getApp(): \pff\App
    {
        return $this->_app;
    }

    /**
     * @param string $moduleName Name of the module to load
     * @return AModule
     * @deprecated Use ModuleManager::loadModule('module_name')
     */
    public function loadModule(string $moduleName): AModule
    {
        return $this->_moduleManager->getModule($moduleName);
    }

    /**
     * @param string $helperName Name of the helper to load
     */
    public function loadHelper(string $helperName): bool
    {
        return $this->_helperManager->load($helperName);
    }

    /**
     * Gets a parameter (GET)
     *
     * @param int|string $index
     * @param string $errorMessage
     * @param int $errorCode
     * @throws PffException
     * @return string
     */
    public function getParam(int|string $index, string $errorMessage = "Page not found", int $errorCode = 404): string
    {
        if (isset($this->_params[$index])) {
            return $this->_params[$index];
        } else {
            throw new PffException($errorMessage, $errorCode);
        }
    }

    /**
     * Registers a BeforeFilter
     *
     * @param string $actionName
     * @param callable $method
     */
    public function registerBeforeFilter(string $actionName, callable $method): void
    {
        $this->_beforeFilters[$actionName][] = $method;
    }

    /**
     * Registers an AfterFilter
     */
    public function registerAfterFilter(string $actionName, callable $method): void
    {
        $this->_afterFilters[$actionName][] = $method;
    }

    /**
     * Executes all the registered beforeFilters for the current action
     */
    public function beforeFilter(): void
    {
        if (!isset($this->_beforeFilters[$this->_action])) {
            return;
        }

        foreach ($this->_beforeFilters[$this->_action] as $method) {
            call_user_func($method);
        }
    }

    /**
     * Execute all the registered afterFilters for the current action
     */
    public function afterFilter(): void
    {
        if (!isset($this->_afterFilters[$this->_action])) {
            return;
        }

        foreach ($this->_afterFilters[$this->_action] as $method) {
            call_user_func($method);
        }
    }

    /**
     * @return AView
     * @throws PffException
     */
    public function getLayout(): AView
    {
        if ($this->_layout) {
            return $this->_layout;
        } else {
            throw new PffException('No layout has been set');
        }
    }

    /**
     * This method RESET the layout (i.e. the first rendereable View in the rendering queue).
     * If a Layout has already been set it will
     *
     * @param $layout AView
     */
    public function setLayout(AView $layout): void
    {
        $this->_layout = $layout;
        if (isset($this->_view[0])) {
            $this->resetViews();
        }
        $this->addView($layout);
    }

    public function resetViews(): void
    {
        unset($this->_view);
        $this->_view = [];
    }

    /**
     * @return AView[]
     */
    public function getViews(): array
    {
        return $this->_view;
    }

    public function getOutput(): IOutputs
    {
        return $this->_output;
    }

    public function setOutput(IOutputs $output): void
    {
        $this->_output = $output;
    }

    public function setIsRenderAction(bool $value): void
    {
        $this->_isRenderAction = $value;
    }

    public function getIsRenderAction(): bool
    {
        return $this->_isRenderAction;
    }
}
