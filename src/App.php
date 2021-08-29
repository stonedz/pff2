<?php

namespace pff;
use pff\Core\HelperManager;
use pff\Core\HookManager;
use pff\Core\ModuleManager;
use pff\Core\ServiceContainer;
use pff\Exception\RoutingException;
use \Symfony\Component\Debug\ErrorHandler;

/**
 * Main app
 *
 * @author paolo.fagni<at>gmail.com
 */
class App {

    /**
     * @var string
     */
    private $_url;

    /**
     * Contains user defined routes for page controllers
     *
     * @var array
     */
    private $_staticRoutes;

    /**
     * Contains user defined routes
     *
     * @var array
     */
    private $_routes;

    /**
     * @var \pff\Config
     */
    private $_config;

    /**
     * @var ModuleManager
     */
    private $_moduleManager;

    /**
     * @var HookManager
     */
    private $_hookManager;

    /**
     * @var HelperManager
     */
    private $_helperManager;

    /**
     * @var string
     */
    private $_action;

    /**
     * @internal param string $url The request URL
     * @internal param Config $config
     * @internal param ModuleManager $moduleManager
     * @internal param HookManager $hookManager
     */
    public function __construct($config = null,
                            $hookmanager = null,
                            $modulemanager = null,
                            $helpermanager = null) {

        if($config){
            $this->_config = $config;
        }                    
        else{
            $this->_config        = ServiceContainer::get('config');
        }

        if($hookmanager){
            $this->_hookManager = $hookmanager;
        }
        else {
            $this->_hookManager   = ServiceContainer::get('hookmanager');
        }

        if($modulemanager) {
            $this->_moduleManager = $modulemanager;
        }
        else {
            $this->_moduleManager = ServiceContainer::get('modulemanager');
        }

        if($helpermanager) {
            $this->_helperManager = $helpermanager;
        }
        else {
            $this->_helperManager = ServiceContainer::get('helpermanager');
        }
    }

    /**
     * Sets error reporting
     */
    public function setErrorReporting() {
        if (true ===  $this->_config->getConfigData('development_environment')) {
            error_reporting(E_ALL);
            ini_set('display_errors', 'On');

            ErrorHandler::register();
        } else {
            error_reporting(E_ALL);
            ini_set('display_errors', 'Off');
            ini_set('log_errors', 'On');
            ini_set('error_log', ROOT . DS . 'tmp' . DS . 'logs' . DS . 'error.log');
        }
    }

    /**
     * Check for Magic Quotes and remove them
     *
     * @param $value array|string
     * @return array|string
     * @codeCoverageIgnore
     */
    private function stripSlashesDeep($value) {
        $value = is_array($value) ? array_map(array($this, 'stripSlashesDeep'), $value) : stripslashes($value);
        return $value;
    }

    /**
     * Check register globals and remove them
     *
     * @codeCoverageIgnore
     */
    public function unregisterGlobals() {
        if (ini_get('register_globals')) {
            $array = array('_SESSION', '_POST', '_GET', '_COOKIE', '_REQUEST', '_SERVER', '_ENV', '_FILES');
            foreach ($array as $value) {
                foreach ($GLOBALS[$value] as $key => $var) {
                    if ($var === $GLOBALS[$key]) {
                        unset($GLOBALS[$key]);
                    }
                }
            }
        }
    }

    /**
     * Adds a static route to a page (the result is something similar to page controller pattern).
     *
     * @param string $request
     * @param string $destinationPage
     * @throws \pff\Exception\RoutingException
     */
    public function addStaticRoute($request, $destinationPage) {
        if (file_exists(PAGES. DS . $destinationPage)) {
            $this->_staticRoutes[$request] = $destinationPage;
        } else {
            throw new RoutingException('Non existant static route specified: ' . $destinationPage);
        }
    }

    /**
     * Adds a non-standard MVC route, for example request xxx to yyy_Controller.
     *
     * @param string $request
     * @param string $destinationController
     * @throws RoutingException
     */
    public function addRoute($request, $destinationController) {
        $explodedDestination = explode('/', $destinationController);
        if (file_exists(CONTROLLERS . DS . ucfirst($explodedDestination[0]) . '_Controller.php')) {
            $this->_routes[$request] = $destinationController;
        } else {
            throw new RoutingException('Non existant MVC route specified: ' . $destinationController);
        }
    }

    /**
     * Apply static routes.
     *
     * @param string $request
     * @return bool True if a match is found
     */
    public function applyStaticRouting(&$request) {
        if (isset($this->_staticRoutes[$request])) {
            $request = $this->_staticRoutes[$request];
            $request = 'app' . DS . 'pages' . DS . $request;
            return true;
        }
        return false;
    }

    /**
     * Apply user-defined MVC routes.
     *
     * @param string $request
     * @param null|string $action If the route has an action specified (ex. admin/show will be filled with "show")
     * @param null $urlArray array of parameters passed to the controller
     * @return bool True if a match is found
     */
    public function applyRouting(&$request, &$action = null, &$urlArray = null) {
        if (isset($this->_routes[strtolower($request)])) {
            $route          = $this->_routes[strtolower($request)];
            $explodedTarget = explode('/', $route);

            if (isset($explodedTarget[1])) { // we have an action for this route!
                $action = $explodedTarget[1];
            }
            $request = $explodedTarget[0];
            $request = ucfirst($request) . '_Controller';
            // Params, if more than 2 elements are specified
            if(count($explodedTarget) > 2){
                $routeParams = array_slice($explodedTarget, 2);
                $urlArray = array_merge($routeParams, $urlArray);
            }
            return true;
        }
        return false;
    }

    /**
     * Runs the application
     */
    public function run() {
        $this->_hookManager->runBeforeSystem();

        $urlArray = explode('/', $this->_url);
        //Deletes last element if empty
        $lastElement = end($urlArray);
        if ($lastElement == '') {
            array_pop($urlArray);
        }
        reset($urlArray);

        // If present take the first element as the controller
        $tmpController = isset($urlArray[0]) ? array_shift($urlArray) : 'index';
        $action = null;

        //Prepare the GET params in order to pass them to the Controller
        $myGet = $_GET;
        if(isset($myGet['url'])){
            unset($myGet['url']);
        }

        if ($this->applyStaticRouting($tmpController)) {
            $this->_hookManager->runBefore(); // Runs before controller hooks
            include(ROOT . DS . $tmpController);
            $this->_hookManager->runAfter(); // Runs after controller hooks
        } elseif ($this->applyRouting($tmpController, $action, $urlArray)) {
            ($action === null) ? $action = 'index' : $action;
            $tmpController = '\\pff\\controllers\\'.$tmpController;
            $controller = new $tmpController($tmpController, $this, $action, array_merge($urlArray,$myGet));
        } elseif (file_exists(ROOT . DS . 'app' . DS . 'controllers' . DS . ucfirst($tmpController) . '_Controller.php')) {
            $action = isset($urlArray[0]) ? array_shift($urlArray) : 'index';
            $controllerClassName = '\\pff\\controllers\\'.ucfirst($tmpController) . '_Controller';
            $controller          = new $controllerClassName($tmpController, $this, $action, array_merge($urlArray,$myGet));
        } else {
            throw new RoutingException('Cannot find a valid controller.', 404);
        }

        if (isset($controller)) {
            $this->_action = $action;
            $this->_moduleManager->setController($controller); // We have a controller, let the modules know about it
            ob_start();
            $this->_hookManager->runBefore(); // Runs before controller hooks
            if ((int)method_exists($controller, $this->_action)) {
                call_user_func_array(array($controller, "beforeAction"), $urlArray);
                call_user_func(array($controller, "beforeFilter"));
                call_user_func_array(array($controller, $this->_action), $urlArray);
                call_user_func(array($controller, "afterFilter"));
                call_user_func_array(array($controller, "afterAction"), $urlArray);
                $this->_hookManager->runAfter(); // Runs after controller hooks
                ob_end_flush();
            } else {
                throw new RoutingException('Not a valid action: ' . $action, 404);
            }
        }
    }

    /**
     * Returns the external path of the application. For example http://www.xxx.com/base_path
     *
     * @return string
     */
    public function getExternalPath() {
        if (true === $this->_config->getConfigData('development_environment')) {
            $extPath = EXT_ROOT . $this->_config->getConfigData('base_path_dev');
        } else {
            $extPath = EXT_ROOT . $this->_config->getConfigData('base_path');
        }
        return $extPath;
    }

    /**
     * @param string $url
     */
    public function setUrl($url) {
        $this->_url = $url;
    }

    /**
     * @return string
     */
    public function getUrl() {
        return $this->_url;
    }

    /**
     * @return array
     */
    public function getRoutes() {
        return $this->_routes;
    }

    /**
     * @return array
     */
    public function getStaticRoutes() {
        return $this->_staticRoutes;
    }

    /**
     * @return \pff\Config
     */
    public function getConfig() {
        return $this->_config;
    }

    /**
     * @param HookManager $hookManager
     */
    public function setHookManager($hookManager) {
        $this->_hookManager = $hookManager;
    }

    /**
     * @return HookManager
     */
    public function getHookManager() {
        return $this->_hookManager;
    }

    /**
     * @param ModuleManager $moduleManager
     */
    public function setModuleManager($moduleManager) {
        $this->_moduleManager = $moduleManager;
    }

    /**
     * @return ModuleManager
     */
    public function getModuleManager() {
        return $this->_moduleManager;
    }

    /**
     * @param HelperManager $helperManager
     */
    public function setHelperManager($helperManager) {
        $this->_helperManager = $helperManager;
    }

    /**
     * @return HelperManager
     */
    public function getHelperManager() {
        return $this->_helperManager;
    }

    /**
     * @return string
     */
    public function getAction() {
        return $this->_action;
    }

    /**
     * @param string $action
     */
    public function setAction($action) {
        $this->_action = $action;
    }
}
