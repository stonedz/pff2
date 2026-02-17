<?php

declare(strict_types=1);

namespace pff\Abs;

use pff\Exception\ModuleException;

/**
 * Abstract class for pff modules
 *
 * @author paolo.fagni<at>gmail.com
 */
abstract class AModule
{
    private string $_moduleName = '';

    private string $_moduleVersion = '';

    private string $_moduleDescription = '';

    /**
     * Array of modules names required by this module
     *
     * @var string[]
     */
    private array $_moduleRequirements = [];

    /**
     * Contains modules required by this module
     *
     * @var AModule[]
     */
    private array $_requiredModules = [];

    private ?\pff\Config $_config = null;

    protected ?AController $_controller = null;

    /**
     * Reference to main app
     */
    protected ?\pff\App $_app = null;

    public function setModuleName(string $moduleName): void
    {
        $this->_moduleName = $moduleName;
    }

    public function getModuleName(): string
    {
        return $this->_moduleName;
    }

    public function setModuleVersion(string $moduleVersion): void
    {
        $this->_moduleVersion = $moduleVersion;
    }

    public function getModuleVersion(): string
    {
        return $this->_moduleVersion;
    }

    public function getModuleDescription(): string
    {
        return $this->_moduleDescription;
    }

    public function setModuleDescription(string $moduleDescription): void
    {
        $this->_moduleDescription = $moduleDescription;
    }

    /**
     * Injects a required module reference to the module
     *
     * @param AModule $module
     */
    public function registerRequiredModule(AModule $module): void
    {
        $this->_requiredModules[strtolower($module->getModuleName())] = $module;
    }

    /**
     * Gets a module
     *
     * @param string $moduleName
     * @return AModule|null
     */
    public function getRequiredModules(string $moduleName): ?AModule
    {
        $moduleName = strtolower($moduleName);
        if (isset($this->_requiredModules[$moduleName])) {
            return $this->_requiredModules[$moduleName];
        } else {
            return null;
        }
    }

    /**
     * @param string[] $moduleRequirements
     */
    public function setModuleRequirements(array $moduleRequirements): void
    {
        $this->_moduleRequirements = $moduleRequirements;
    }

    /**
     * @return string[]
     */
    public function getModuleRequirements(): array
    {
        return $this->_moduleRequirements;
    }

    public function setConfig(\pff\Config $config): void
    {
        $this->_config = $config;
    }

    public function getConfig(): ?\pff\Config
    {
        return $this->_config;
    }

    public function setController(AController $controller): void
    {
        $this->_controller = $controller;
    }

    public function getController(): ?AController
    {
        return $this->_controller;
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
     * Reads the configuration file ad returns a configuration array
     *
     * @param string $configFile The module filename
     * @throws ModuleException
     * @return array
     */
    public function readConfig(string $configFile): array
    {
        $yamlParser = new \Symfony\Component\Yaml\Parser();
        $userConfPath = ROOT . DS . 'app' . DS . 'config' . DS . 'modules' . DS . $configFile;
        $userCustomPath = ROOT . DS . 'app' . DS . 'modules' . DS . $configFile;
        $composerConfPath = ROOT . DS . 'modules' . DS . $configFile;
        $libConfPath = ROOT_LIB . DS . 'src' . DS . 'modules' . DS . $configFile;
        if (file_exists($userConfPath)) {
            $confPath = $userConfPath;
        } elseif (file_exists($userCustomPath)) {
            $confPath = $userCustomPath;
        } elseif (file_exists($composerConfPath)) {
            $confPath = $composerConfPath;
        } elseif (file_exists($libConfPath)) {
            $confPath = $libConfPath;
        } else {
            throw new ModuleException("Module configuration file not found!");
        }

        try {
            $conf = $yamlParser->parse(file_get_contents($confPath));
        } catch (\Symfony\Component\Yaml\Exception\ParseException $e) {
            throw new ModuleException("Unable to parse module configuration
                                            file for AutomaticHeaderFooter module: " . $e->getMessage());
        }
        return $conf;
    }
}
