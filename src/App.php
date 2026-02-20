<?php

declare(strict_types=1);

namespace pff;

use pff\Core\HelperManager;
use pff\Core\HookManager;
use pff\Core\ModuleManager;
use pff\Core\ServiceContainer;
use pff\Exception\RoutingException;

/**
 * Main app
 *
 * @author paolo.fagni<at>gmail.com
 */
class App
{
    private string $_url = '';

    /**
     * Contains user defined routes for page controllers
     *
     * @var array<string, string>
     */
    private array $_staticRoutes = [];

    /**
     * Contains user defined routes
     *
     * @var array<string, string>
     */
    private array $_routes = [];

    private Config $_config;

    private ModuleManager $_moduleManager;

    private HookManager $_hookManager;

    private HelperManager $_helperManager;

    private string $_action = '';

    /**
     * @internal param string $url The request URL
     * @internal param Config $config
     * @internal param ModuleManager $moduleManager
     * @internal param HookManager $hookManager
     */
    public function __construct(
        ?Config $config = null,
        ?HookManager $hookmanager = null,
        ?ModuleManager $modulemanager = null,
        ?HelperManager $helpermanager = null
    ) {
        if ($config) {
            $this->_config = $config;
        } else {
            $this->_config = ServiceContainer::get('config');
        }

        if ($hookmanager) {
            $this->_hookManager = $hookmanager;
        } else {
            $this->_hookManager = ServiceContainer::get('hookmanager');
        }

        if ($modulemanager) {
            $this->_moduleManager = $modulemanager;
        } else {
            $this->_moduleManager = ServiceContainer::get('modulemanager');
        }

        if ($helpermanager) {
            $this->_helperManager = $helpermanager;
        } else {
            $this->_helperManager = ServiceContainer::get('helpermanager');
        }
    }

    /**
     * Sets error reporting
     */
    public function setErrorReporting(): void
    {
        if (true === $this->_config->getConfigData('development_environment')) {
            if ($this->_config->getConfigData('show_all_errors') === true) {
                error_reporting(E_ALL & ~E_DEPRECATED & ~E_NOTICE);
                ini_set('display_errors', 'On');
            } else {
                error_reporting(E_ALL);
                ini_set('display_errors', 'On');
            }
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
     * @deprecated No longer needed since PHP 5.4. Will be removed in pff2 5.0.
     */
    private function stripSlashesDeep(array|string $value): array|string
    {
        $value = is_array($value) ? array_map([$this, 'stripSlashesDeep'], $value) : stripslashes($value);
        return $value;
    }

    /**
     * Check register globals and remove them
     *
     * @codeCoverageIgnore
     * @deprecated No longer needed since PHP 5.4. Will be removed in pff2 5.0.
     */
    public function unregisterGlobals(): void
    {
        if (ini_get('register_globals')) {
            $array = ['_SESSION', '_POST', '_GET', '_COOKIE', '_REQUEST', '_SERVER', '_ENV', '_FILES'];
            foreach ($array as $value) {
                foreach ($GLOBALS[$value] as $key => $var) {
                    if ($var === $GLOBALS[$key]) {
                        unset($GLOBALS[$key]);
                    }
                }
            }
        }
    }

    // -------------------------------------------------------------------------
    //  Safe input accessors
    // -------------------------------------------------------------------------

    /**
     * Gets a value from the $_GET superglobal with optional sanitization.
     *
     * @param string $key Parameter name
     * @param mixed $default Default value when key is absent
     * @param int $filter A PHP filter constant (e.g. FILTER_SANITIZE_SPECIAL_CHARS). Pass FILTER_DEFAULT to skip.
     * @return mixed
     */
    public function getQuery(string $key, mixed $default = null, int $filter = FILTER_DEFAULT): mixed
    {
        if (!isset($_GET[$key])) {
            return $default;
        }
        $value = filter_input(INPUT_GET, $key, $filter);
        return ($value === false || $value === null) ? $default : $value;
    }

    /**
     * Gets a value from the $_POST superglobal with optional sanitization.
     *
     * @param string $key Parameter name
     * @param mixed $default Default value when key is absent
     * @param int $filter A PHP filter constant (e.g. FILTER_SANITIZE_SPECIAL_CHARS). Pass FILTER_DEFAULT to skip.
     * @return mixed
     */
    public function getPost(string $key, mixed $default = null, int $filter = FILTER_DEFAULT): mixed
    {
        if (!isset($_POST[$key])) {
            return $default;
        }
        $value = filter_input(INPUT_POST, $key, $filter);
        return ($value === false || $value === null) ? $default : $value;
    }

    /**
     * Gets a value from the $_SERVER superglobal with optional sanitization.
     *
     * @param string $key Server variable name (e.g. 'REQUEST_METHOD')
     * @param mixed $default Default value when key is absent
     * @param int $filter A PHP filter constant. Pass FILTER_DEFAULT to skip.
     * @return mixed
     */
    public function getServer(string $key, mixed $default = null, int $filter = FILTER_DEFAULT): mixed
    {
        if (!isset($_SERVER[$key])) {
            return $default;
        }
        $value = filter_input(INPUT_SERVER, $key, $filter);
        return ($value === false || $value === null) ? $default : $value;
    }

    /**
     * Gets a value from the $_COOKIE superglobal with optional sanitization.
     *
     * @param string $key Cookie name
     * @param mixed $default Default value when key is absent
     * @param int $filter A PHP filter constant. Pass FILTER_DEFAULT to skip.
     * @return mixed
     */
    public function getCookie(string $key, mixed $default = null, int $filter = FILTER_DEFAULT): mixed
    {
        if (!isset($_COOKIE[$key])) {
            return $default;
        }
        $value = filter_input(INPUT_COOKIE, $key, $filter);
        return ($value === false || $value === null) ? $default : $value;
    }

    /**
     * Adds a static route to a page (the result is something similar to page controller pattern).
     *
     * @param string $request
     * @param string $destinationPage
     * @throws \pff\Exception\RoutingException
     */
    public function addStaticRoute(string $request, string $destinationPage): void
    {
        if (file_exists(PAGES . DS . $destinationPage)) {
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
    public function addRoute(string $request, string $destinationController): void
    {
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
    public function applyStaticRouting(string &$request): bool
    {
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
    public function applyRouting(string &$request, ?string &$action = null, ?array &$urlArray = null): bool
    {
        if (isset($this->_routes[strtolower($request)])) {
            $route = $this->_routes[strtolower($request)];
            $explodedTarget = explode('/', $route);

            if (isset($explodedTarget[1])) { // we have an action for this route!
                $action = $explodedTarget[1];
            }
            $request = $explodedTarget[0];
            $request = ucfirst($request) . '_Controller';
            // Params, if more than 2 elements are specified
            if (count($explodedTarget) > 2) {
                $routeParams = array_slice($explodedTarget, 2);
                $urlArray = array_merge($routeParams, $urlArray);
            }
            return true;
        }
        return false;
    }

        /**
     * Returns the APCu cache key for routes, based on the config file hash.
     */
    private function routeCacheKey(): string
    {
        $appName = (string) ($this->_config->getConfigData('app_name') ?? 'pff2');
        $configFile = ROOT . DS . 'app' . DS . 'config' . DS . 'config.user.php';
        $hash = file_exists($configFile) ? sha1_file($configFile) : 'nohash';
        return $appName . ':routes:' . $hash;
    }

    /**
     * Runs the application
     */
    public function run(): void
    {
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
        if (isset($myGet['url'])) {
            unset($myGet['url']);
        }

        if ($this->applyStaticRouting($tmpController)) {
            $this->_hookManager->runBefore(); // Runs before controller hooks
            include(ROOT . DS . $tmpController);
            $this->_hookManager->runAfter(); // Runs after controller hooks
        } elseif ($this->applyRouting($tmpController, $action, $urlArray)) {
            $action ??= 'index';
            $tmpController = '\\pff\\controllers\\' . $tmpController;
            $controller = new $tmpController($tmpController, $this, $action, array_merge($urlArray, $myGet));
        } elseif (file_exists(ROOT . DS . 'app' . DS . 'controllers' . DS . ucfirst((string) $tmpController) . '_Controller.php')) {
            $action = isset($urlArray[0]) ? array_shift($urlArray) : 'index';
            $controllerClassName = '\\pff\\controllers\\' . ucfirst((string) $tmpController) . '_Controller';
            $controller = new $controllerClassName($tmpController, $this, $action, array_merge($urlArray, $myGet));
        } else {
            throw new RoutingException('Cannot find a valid controller.', 404);
        }

        if (isset($controller)) {
            $this->_action = $action;
            $this->_moduleManager->setController($controller); // We have a controller, let the modules know about it
            ob_start();
            $this->_hookManager->runBefore(); // Runs before controller hooks
            if ((int) method_exists($controller, $this->_action)) {
                call_user_func_array([$controller, "beforeAction"], $urlArray);
                call_user_func([$controller, "beforeFilter"]);
                call_user_func_array([$controller, $this->_action], $urlArray);
                call_user_func([$controller, "afterFilter"]);
                call_user_func_array([$controller, "afterAction"], $urlArray);
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
    public function getExternalPath(): string
    {
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
    public function setUrl(string $url): void
    {
        $this->_url = $url;
    }

    public function getUrl(): string
    {
        return $this->_url;
    }

    /**
     * @return array<string, string>
     */
    public function getRoutes(): array
    {
        return $this->_routes;
    }

    /**
     * @return array<string, string>
     */
    public function getStaticRoutes(): array
    {
        return $this->_staticRoutes;
    }

    public function loadRouteCache(): bool
    {
        if (!function_exists('apcu_fetch')) {
            return false;
        }

        $success = false;
        $cachedRoutes = apcu_fetch($this->getRouteCacheKey(), $success);
        if ($success !== true || !is_array($cachedRoutes)) {
            return false;
        }

        if (!isset($cachedRoutes['routes'], $cachedRoutes['static_routes'])) {
            return false;
        }

        if (!is_array($cachedRoutes['routes']) || !is_array($cachedRoutes['static_routes'])) {
            return false;
        }

        $this->_routes = $cachedRoutes['routes'];
        $this->_staticRoutes = $cachedRoutes['static_routes'];

        return true;
    }

    public function storeRouteCache(): bool
    {
        if (!function_exists('apcu_store')) {
            return false;
        }

        return apcu_store($this->getRouteCacheKey(), [
            'routes' => $this->_routes,
            'static_routes' => $this->_staticRoutes,
        ]);
    }

    public function clearRouteCache(): bool
    {
        if (!function_exists('apcu_delete')) {
            return false;
        }

        return apcu_delete($this->getRouteCacheKey());
    }

    private function getRouteCacheKey(): string
    {
        $appName = (string) $this->_config->getConfigData('app_name');
        if ($appName === '') {
            $appName = 'pff';
        }

        $configFile = ROOT . DS . 'app' . DS . 'config' . DS . 'config.user.php';
        $configHash = is_file($configFile) ? sha1_file($configFile) : 'no-config';

        return $appName . ':routes:' . $configHash;
    }

    public function getConfig(): Config
    {
        return $this->_config;
    }

    public function setHookManager(HookManager $hookManager): void
    {
        $this->_hookManager = $hookManager;
    }

    public function getHookManager(): HookManager
    {
        return $this->_hookManager;
    }

    public function setModuleManager(ModuleManager $moduleManager): void
    {
        $this->_moduleManager = $moduleManager;
    }

    public function getModuleManager(): ModuleManager
    {
        return $this->_moduleManager;
    }

    public function setHelperManager(HelperManager $helperManager): void
    {
        $this->_helperManager = $helperManager;
    }

    public function getHelperManager(): HelperManager
    {
        return $this->_helperManager;
    }

    public function getAction(): string
    {
        return $this->_action;
    }

    public function setAction(string $action): void
    {
        $this->_action = $action;
    }
}
