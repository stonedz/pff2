<?php

namespace pff\Core;
use pff\Abs\AController;
use pff\Abs\AModule;
use pff\Exception\ModuleException;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Parser;

/**
 * Manages pff modules
 *
 * @author paolo.fagni<at>gmail.com
 */
class ModuleManager {

    /**
     * @var \pff\Config
     */
    private $_config;

    /**
     * @var HookManager
     */
    private $_hookManager;

    /**
     * @var \Symfony\Component\Yaml\Parser
     */
    private $_yamlParser;

    /**
     * Contains loaded modules
     *
     * @var AModule[]
     */
    static private $_modules;

    /**
     * Reference to main app
     *
     * @var \pff\App
     */
    private $_app;

    public function __construct() {
        $this->_config      = ServiceContainer::get('config');
        $this->_yamlParser  = new Parser();
        $this->_hookManager = ServiceContainer::get('hookmanager');
    }

    /**
     * Autoload modules specified in config files
     *
     * @return void
     */
    public static function initModules() {
        $cfg = ServiceContainer::get('config');
        $moduleList = $cfg->getConfigData('modules');
        if (count($moduleList) > 0) {
            foreach ($moduleList as $moduleName) {
                self::loadModule($moduleName);
            }
        }
    }

    /**
     * Checks for php extensions for pff modules
     *
     * @var $phpExtensions array An array of php extensions names
     * @throws ModuleException
     */
    private static function checkPhpExtensions($phpExtensions) {
        foreach ($phpExtensions as $extension) {
            if (!extension_loaded($extension)) {
                throw new ModuleException("Module loading failed! A module needs the following php extension in order to load: " . $extension);
            }
        }
    }

    /**
     * Loads a module and its dependencies and then returns the module reference
     *
     * @param string $moduleName
     * @return bool|AModule
     * @throws ModuleException
     */
    public static function loadModule($moduleName) {
        $moduleConf = self::getModuleConf($moduleName);

        try {

            if (isset($moduleConf['requires_php_extension']) && is_array($moduleConf['requires_php_extension'])) {
                self::checkPhpExtensions($moduleConf['requires_php_extension']);
            }

            $tmpModule = new \ReflectionClass('\\pff\\modules\\' . $moduleConf['class']);
            if ($tmpModule->isSubclassOf('\\pff\\Abs\\AModule')) {
                $moduleName = strtolower($moduleConf['name']);

                if (isset(self::$_modules[$moduleName])) { //Module has already been loaded
                    return self::$_modules[$moduleName];
                }

                self::$_modules[$moduleName] = $tmpModule->newInstance();
                self::$_modules[$moduleName]->setModuleName($moduleConf['name']);
                self::$_modules[$moduleName]->setModuleVersion($moduleConf['version']);
                self::$_modules[$moduleName]->setModuleDescription($moduleConf['desc']);
                self::$_modules[$moduleName]->setConfig(ServiceContainer::get('config'));
                self::$_modules[$moduleName]->setApp(ServiceContainer::get('app'));
                (isset($moduleConf['runBefore']))? $moduleLoadBefore = $moduleConf['runBefore']: $moduleLoadBefore = null ;

                if (isset ($moduleConf['requires']) && is_array($moduleConf['requires'])) {
                    self::$_modules[$moduleName]->setModuleRequirements($moduleConf['requires']);
                    foreach ($moduleConf['requires'] as $requiredModuleName) {
                        self::loadModule($requiredModuleName);
                        self::$_modules[$moduleName]->registerRequiredModule(self::$_modules[$requiredModuleName]);
                    }
                }

                if ($tmpModule->isSubclassOf('\pff\Iface\IHookProvider')) {
                    ServiceContainer::get('hookmanager')->registerHook(self::$_modules[$moduleName], $moduleName, $moduleLoadBefore);
                }

                return self::$_modules[$moduleName];

            } else {
                throw new ModuleException("Invalid module: " . $moduleConf['name']);
            }
        }
        catch (\ReflectionException $e) {
            throw new ModuleException("Unable to create module instance: " . $e->getMessage());
        }

    }

    /**
     * Get the instance of desired module
     *
     * @param string $moduleName
     * @throws ModuleException
     * @return AModule The requested module
     */
    public function getModule($moduleName) {
        $moduleName = strtolower($moduleName);
        if (isset(self::$_modules[$moduleName])) {
            return self::$_modules[$moduleName];
        } else {
            try {
                return $this->loadModule($moduleName);
            }
            catch (\Exception $e) {
                throw new ModuleException("Cannot find requested module: $moduleName");
            }
        }
    }

    /**
     * Checks if a module is currently loaded
     *
     * @param string $moduleName
     * @return bool
     */
    public static function isLoaded($moduleName) {
        if(isset(self::$_modules[$moduleName])){
            return true;
        }
        else {
            return false;
        }
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
     * Sets the Controller for each module
     */
    public function setController(AController $controller) {
        if (count(self::$_modules) > 0) {
            foreach (self::$_modules as $module) {
                $module->setController($controller);
            }
        }
    }

    /**
     * @param \pff\App $app
     */
    public function setApp($app) {
        $this->_app = $app;
    }

    /**
     * @return \pff\App
     */
    public function getApp() {
        return $this->_app;
    }

    /**
     * @param $moduleName
     * @return mixed
     * @throws ModuleException
     * @throws \pff\Exception\ConfigException
     */
    private static function getModuleConf($moduleName) {
        $key = ServiceContainer::get('config')->getConfigData('app_name') . '-config-' . md5($moduleName);
        if (extension_loaded('apc') && apc_exists($key)) {
            $moduleConf = apc_fetch($key);
            return $moduleConf;
        } else {
            $moduleFilePathUser = ROOT . DS . 'app' . DS . 'modules' . DS . $moduleName . DS . 'module.yaml';
            $moduleFilePathPff = ROOT_LIB . DS . 'src' . DS . 'modules' . DS . $moduleName . DS . 'module.yaml';
            $moduleComposerPath = ROOT . DS . 'modules' . DS . $moduleName . DS . 'module.yaml';

            if (file_exists($moduleFilePathUser)) {
                $moduleFilePath = $moduleFilePathUser;
            } elseif (file_exists($moduleComposerPath)) {
                $moduleFilePath = $moduleComposerPath;
            } elseif (file_exists($moduleFilePathPff)) {
                $moduleFilePath = $moduleFilePathPff;
            } else {
                throw new ModuleException("Specified module \"" . $moduleName . "\" does not exist");
            }

            try {
                $moduleConf = ServiceContainer::get('yamlparser')->parse(file_get_contents($moduleFilePath));
            } catch (ParseException $e) {
                throw new ModuleException("Unable to parse module configuration
                                                file for $moduleName: " . $e->getMessage());
            }

            if (extension_loaded('apc') && !apc_exists($key)) {
                apc_store($key, $moduleConf);
            }

            return $moduleConf;
        }
    }
}
