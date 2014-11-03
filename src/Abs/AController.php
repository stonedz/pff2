<?php

namespace pff\Abs;
use Assetic\Cache\ArrayCache;
use Doctrine\Common\Cache\ApcCache;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManager;
use pff\App;
use pff\Core\HelperManager;
use pff\Core\ModuleManager;
use pff\Exception\PffException;
use pff\Exception\ViewException;

/**
 * Every controller must implement this abstract class
 *
 * @author paolo.fagni<at>gmail.com
 */
abstract class AController {

    /**
     * @var string
     */
    protected $_controllerName;

    /**
     * @var string
     */
    protected $_action;

    /**
     * @var AView
     */
    protected $_view;

    /**
     * @var \pff\Config
     */
    protected $_config;

    /**
     * The app that is running
     *
     * @var \pff\App
     */
    protected $_app;

    /**
     * @var EntityManager
     */
    public $_em;

    /**
     * Array of parameters passed to the specified action
     *
     * @var array
     */
    protected $_params;

    /**
     * Reference to app's module manager
     *
     * Used to access loaded modules or load new modules
     *
     * @var ModuleManager
     */
    protected $_moduleManager;

    /**
     * @var HelperManager
     */
    protected $_helperManager;

    /**
     * Contains the registered beforeFilters
     *
     * @var array
     */
    protected $_beforeFilters;

    /**
     * Contains the registered afterFilters
     *
     * @var array
     */
    protected $_afterFilters;

    /**
     * May contain the default layout (used by main_layout module for example)
     *
     * @var AView
     */
    protected $_layout;

    /**
     * Creates a controller
     *
     * @param string $controllerName The controller's name (used to load correct model)
     * @param \pff\App $app
     * @param string $action Action to perform
     * @param array $params An array with parameters passed to the action
     * @internal param \pff\Config $cfg App configuration
     */
    public function __construct($controllerName, App $app, $action = 'index', $params = array()) {
        $this->_controllerName = $controllerName;
        $this->_action         = $action;
        $this->_app            = $app;
        $this->_config         = $app->getConfig(); //Even if we have an \pff\App reference we keep this for legacy reasons.
        $this->_params         = $params;
        $this->_moduleManager  = $this->_app->getModuleManager();
        $this->_helperManager  = $this->_app->getHelperManager();
        $this->_layout         = null;
        $this->_view           = array();

        if ($this->_config->getConfigData('orm')) {
            $this->initORM();
        }

        $this->initController();
    }

    /**
     * Override this method if you want to init your controller
     *
     * @return bool
     */
    public function initController() {
        return true;
    }

    /**
     * Initializes Doctrine entity manager
     */
    private function initORM() {

        if (true === $this->_config->getConfigData('development_environment')) {
            $cache = new ArrayCache();
        } else {
            $cache = new ApcCache(array('prefix'=>$this->_app->getConfig()->getConfigData('app_name')));
        }

        $config = new Configuration();
        $config->setMetadataCacheImpl($cache);
        $driverImpl = $config->newDefaultAnnotationDriver(ROOT . DS . 'app' . DS . 'models');
        $config->setMetadataDriverImpl($driverImpl);
        $config->setQueryCacheImpl($cache);
        $config->setProxyDir(ROOT . DS . 'app' . DS . 'proxies');
        $config->setProxyNamespace('pff\proxies');

        if (true === $this->_config->getConfigData('development_environment')) {
            $config->setAutoGenerateProxyClasses(true);
            $connectionOptions = $this->_config->getConfigData('databaseConfigDev');
        } else {
            $config->setAutoGenerateProxyClasses(false);
            $connectionOptions = $this->_config->getConfigData('databaseConfig');
        }


        $this->_em = EntityManager::create($connectionOptions, $config);

        $platform = $this->_em->getConnection()->getDatabasePlatform();
        $platform->registerDoctrineTypeMapping('enum', 'string');
    }

    /**
     * Method executed before the action
     */
    public function beforeAction() {
    }

    /**
     * Method executed after the action
     */
    public function afterAction() {
    }

    /**
     * Adds a view
     *
     * @param AView $view
     */
    public function addView(AView $view) {
        $this->_view[] = $view;
    }

    /**
     * Adds a view at the top of the stack
     *
     * @param AView $view
     */
    public function addViewPre(AView $view) {
        array_unshift($this->_view, $view);
    }

    /**
     * Called before the controller is deleted.
     *
     * The view's render method is called for each view registered.
     *
     * @throws ViewException
     */
    public function __destruct() {

        if (isset($this->_view)) {
            if (is_array($this->_view)) {
                $this->_app->getHookManager()->runBeforeView();
                foreach ($this->_view as $view) {
                    $view->render();
                }
                $this->_app->getHookManager()->runAfterView();
            } elseif (is_a($this->_view, '\\pff\\AView')) {
                $this->_app->getHookManager()->runBeforeView();
                $this->_view->render();
                $this->_app->getHookManager()->runAfterView();
            } else {
                throw new ViewException("The specified View is not valid.");
            }
        }
    }

    /**
     * All controllers should at least implement an index
     *
     * @abstract
     * @return mixed
     */
    abstract public function index();

    /**
     * @return string
     */
    public function getControllerName() {
        return $this->_controllerName;
    }

    /**
     * @return string
     */
    public function getAction() {
        return $this->_action;
    }

    /**
     * @return \pff\App
     */
    public function getApp() {
        return $this->_app;
    }

    /**
     * @param string $moduleName Name of the module to load
     * @return AModule
     */
    public function loadModule($moduleName) {
        return $this->_moduleManager->getModule($moduleName);
    }

    /**
     * @param string $helperName Name of the helper to load
     * @return bool
     */
    public function loadHelper($helperName) {
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
    public function getParam($index, $errorMessage = "Page not found", $errorCode = 404) {
        if(isset($this->_params[$index])){
            return $this->_params[$index];
        }
        else{
             throw new PffException($errorMessage, $errorCode);
        }
    }

    /**
     * Registers a BeforeFilter
     *
     * @param string $actionName
     * @param \callable $method
     */
    public function registerBeforeFilter($actionName,$method) {
        $this->_beforeFilters[$actionName][] = $method;
    }

    /**
     * Registers an AfterFilter
     *
     * @param string $actionName
     * @param \callable $method
     */
    public function registerAfterFilter($actionName, $method) {
        $this->_afterFilters[$actionName][] = $method;
    }

    /**
     * Executes all the registered beforeFilters for the current action
     */
    public function beforeFilter() {
        if(!isset($this->_beforeFilters[$this->_action])) {
            return false;
        }

        foreach($this->_beforeFilters[$this->_action] as $method) {
            call_user_func($method);
        }
    }

    /**
     * Execute all the registered afterFilters for the current action
     */
    public function afterFilter() {
        if(!isset($this->_afterFilters[$this->_action])) {
            return false;
        }

        foreach($this->_afterFilters[$this->_action] as $method) {
            call_user_func($method);
        }
    }

    /**
     * @return AView
     * @throws PffException
     */
    public function getLayout(){
        if($this->_layout) {
            return $this->_layout;
        }
        else {
            throw new PffException('No layout has been set');
        }
    }

    /**
     * This method RESET the layout (i.e. the first rendereable View in the rendering queue).
     * If a Layout has already been set it will
     *
     * @param $layout AView
     */
    public function setLayout($layout){
        $this->_layout = $layout;
        if(isset($this->_view[0])) {
            $this->resetViews();
        }
        $this->addView($layout);
    }

    public function resetViews() {
        unset($this->_view);
        $this->_view = array();
    }

    public function getViews() {
        return $this->_view;
    }
}
