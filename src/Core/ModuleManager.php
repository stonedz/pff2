<?php

declare(strict_types=1);

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
class ModuleManager
{
    private readonly \pff\Config $_config;

    private HookManager $_hookManager;

    private readonly \Symfony\Component\Yaml\Parser $_yamlParser;

    /**
     * Contains loaded modules
     *
     * @var AModule[]
     */
    private static array $_modules = [];

    /**
     * Reference to main app
     */
    private ?\pff\App $_app = null;

    public function __construct()
    {
        $this->_config = ServiceContainer::get('config');
        $this->_yamlParser = new Parser();
        $this->_hookManager = ServiceContainer::get('hookmanager');
    }

    public static function initModules(): void
    {
        $cfg = ServiceContainer::get('config');
        $moduleList = $cfg->getConfigData('modules');
        if (count($moduleList) > 0) {
            foreach ($moduleList as $moduleName) {
                self::loadModule($moduleName);
            }
        }
    }

    /**
     * @param string[] $phpExtensions
     */
    private static function checkPhpExtensions(array $phpExtensions): void
    {
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
     * @return AModule
     * @throws ModuleException
     */
    public static function loadModule(string $moduleName): AModule
    {
        $moduleConf = self::getModuleConf($moduleName);

        try {
            if (isset($moduleConf['requires_php_extension']) && is_array($moduleConf['requires_php_extension'])) {
                self::checkPhpExtensions($moduleConf['requires_php_extension']);
            }

            $tmpModule = new \ReflectionClass('\\pff\\modules\\' . $moduleConf['class']);
            if ($tmpModule->isSubclassOf('\\pff\\Abs\\AModule')) {
                $moduleName = strtolower((string) $moduleConf['name']);

                if (isset(self::$_modules[$moduleName])) { //Module has already been loaded
                    return self::$_modules[$moduleName];
                }

                self::$_modules[$moduleName] = $tmpModule->newInstance();
                self::$_modules[$moduleName]->setModuleName($moduleConf['name']);
                self::$_modules[$moduleName]->setModuleVersion($moduleConf['version']);
                self::$_modules[$moduleName]->setModuleDescription($moduleConf['desc']);
                self::$_modules[$moduleName]->setConfig(ServiceContainer::get('config'));
                self::$_modules[$moduleName]->setApp(ServiceContainer::get('app'));
                (isset($moduleConf['runBefore'])) ? $moduleLoadBefore = $moduleConf['runBefore'] : $moduleLoadBefore = null;

                if (isset($moduleConf['requires']) && is_array($moduleConf['requires'])) {
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
        } catch (\ReflectionException $e) {
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
    public function getModule(string $moduleName): AModule
    {
        $moduleName = strtolower($moduleName);
        if (isset(self::$_modules[$moduleName])) {
            return self::$_modules[$moduleName];
        } else {
            try {
                return static::loadModule($moduleName);
            } catch (\Exception) {
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
    public static function isLoaded(string $moduleName): bool
    {
        if (isset(self::$_modules[$moduleName])) {
            return true;
        } else {
            return false;
        }
    }

    public function setHookManager(HookManager $hookManager): void
    {
        $this->_hookManager = $hookManager;
    }

    public function getHookManager(): HookManager
    {
        return $this->_hookManager;
    }

    /**
     * Sets the Controller for each module
     */
    public function setController(AController $controller): void
    {
        if (count(self::$_modules) > 0) {
            foreach (self::$_modules as $module) {
                $module->setController($controller);
            }
        }
    }

    public function setApp(\pff\App $app): void
    {
        $this->_app = $app;
    }

    public function getApp(): ?\pff\App
    {
        return $this->_app;
    }

    /**
     * @return array<string, mixed>
     */
    private static function getModuleConf(string $moduleName): array
    {
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
