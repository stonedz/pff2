<?php

namespace pff\Core;

use pff\Exception\HookException;
use pff\Iface\IAfterHook;
use pff\Iface\IAfterViewHook;
use pff\Iface\IBeforeHook;
use pff\Iface\IBeforeSystemHook;
use pff\Iface\IBeforeViewHook;
use pff\Iface\IHookProvider;

/**
 * Hook mediator
 *
 * @author paolo.fagni<at>gmail.com
 */
class HookManager
{
    /**
     * Array of hooks to be executed before system startup
     *
     * @var IBeforeSystemHook[]
     */
    private $_beforeSystem;

    /**
     * Array of hooks to be executed before the controller
     *
     * @var IBeforeHook[]
     */
    private $_beforeController;

    /**
     * Array of hooks to be executed after the controller
     *
     * @var IAfterHook[]
     */
    private $_afterController;

    /**
     * Array of hooks to be executed before the Views are rendered
     *
     * @var IBeforeViewHook[]
     */
    private $_beforeView;


    /**
     * Array of hooks to be executed after the Views are rendered
     *
     * @var IAfterViewHook[]
     */
    private $_afterView;

    /**
     * @var \pff\Config
     */
    private $_config;

    public function __construct($conf = null)
    {
        if ($conf) {
            $this->_config = $conf;
        } else {
            $this->_config = ServiceContainer::get('config');
        }
    }

    /**
     * Registers a hook provider
     *
     * @param IHookProvider $prov Hook provider (module)
     * @param string $moduleName Name of the module
     * @param string|null $loadBefore Hook must be run before specified module name
     * @throws HookException
     */
    public function registerHook(IHookProvider $prov, $moduleName, $loadBefore = null)
    {
        $found = false;

        if (is_a($prov, '\\pff\\Iface\\IBeforeHook')) {
            $found = $this->addHook($this->_beforeController, $prov, $moduleName, $loadBefore);
        }

        if (is_a($prov, '\\pff\\Iface\\IAfterHook')) {
            $found = $this->addHook($this->_afterController, $prov, $moduleName, $loadBefore);
        }

        if (is_a($prov, '\\pff\\Iface\\IBeforeSystemHook')) {
            $found = $this->addHook($this->_beforeSystem, $prov, $moduleName, $loadBefore);
        }

        if (is_a($prov, '\\pff\\Iface\\IBeforeViewHook')) {
            $found = $this->addHook($this->_beforeView, $prov, $moduleName, $loadBefore);
        }

        if (is_a($prov, '\\pff\\Iface\\IAfterViewHook')) {
            $found = $this->addHook($this->_afterView, $prov, $moduleName, $loadBefore);
        }

        if (!$found) {
            throw new HookException("Cannot add given class as a hook provider: ". get_class($prov));
        }
    }

    /**
     * @param array $repository
     * @param IHookProvider $module
     * @param string $moduleName
     * @param string|null $runBeforeModule
     * @return bool
     */
    private function addHook(&$repository, $module, $moduleName, $runBeforeModule)
    {
        if ($runBeforeModule === null  || !array_key_exists($runBeforeModule, $repository)) {
            $repository[$moduleName] = $module;
            return true;
        } else {
            $new = [];
            foreach ($repository as $k => $v) {
                if ($k === $runBeforeModule) {
                    $new[$moduleName] = $module;
                }
                $new[$k] = $v;
            }
            $repository = $new;
            return true;
        }
    }

    /**
     * Executes the registered methods (before the system)
     *
     * @return void
     */
    public function runBeforeSystem()
    {
        if ($this->_beforeSystem !== null) {
            foreach ($this->_beforeSystem as $hookProvider) {
                $hookProvider->doBeforeSystem();
            }
        }
    }

    /**
     * Executes the registered methods (before the controller)
     *
     * @return void
     */
    public function runBefore()
    {
        if ($this->_beforeController !== null) {
            foreach ($this->_beforeController as $hookProvider) {
                $hookProvider->doBefore();
            }
        }
    }

    /**
     * Executes the registered methods (after the controller)
     *
     * @return void
     */
    public function runAfter()
    {
        if ($this->_afterController !== null) {
            foreach ($this->_afterController as $hookProvider) {
                $hookProvider->doAfter();
            }
        }
    }

    /**
     * Executes the registered methods (before the View)
     *
     * @return void
     */
    public function runBeforeView($context = null)
    {
        if ($this->_beforeView !== null) {
            foreach ($this->_beforeView as $hookProvider) {
                $hookProvider->doBeforeView($context);
            }
        }
    }

    /**
     * Executes the registered methods (after the View)
     *
     * @return void
     */
    public function runAfterView()
    {
        if ($this->_afterView !== null) {
            foreach ($this->_afterView as $hookProvider) {
                $hookProvider->doAfterView();
            }
        }
    }

    /**
     * @return \pff\IAfterHook[]
     */
    public function getAfterController()
    {
        return $this->_afterController;
    }

    /**
     * @return IBeforeHook[]
     */
    public function getBeforeController()
    {
        return $this->_beforeController;
    }

    /**
     * @return IBeforeSystemHook[]
     */
    public function getBeforeSystem()
    {
        return $this->_beforeSystem;
    }

    /**
     * @return IAfterViewHook[]
     */
    public function getAfterView()
    {
        return $this->_afterView;
    }

    /**
     * @return IBeforeViewHook[]
     */
    public function getBeforeView()
    {
        return $this->_beforeView;
    }
}
