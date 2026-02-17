<?php

declare(strict_types=1);

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
    private array $_beforeSystem = [];

    /**
     * Array of hooks to be executed before the controller
     *
     * @var IBeforeHook[]
     */
    private array $_beforeController = [];

    /**
     * Array of hooks to be executed after the controller
     *
     * @var IAfterHook[]
     */
    private array $_afterController = [];

    /**
     * Array of hooks to be executed before the Views are rendered
     *
     * @var IBeforeViewHook[]
     */
    private array $_beforeView = [];


    /**
     * Array of hooks to be executed after the Views are rendered
     *
     * @var IAfterViewHook[]
     */
    private array $_afterView = [];

    private \pff\Config $_config;

    public function __construct(?\pff\Config $conf = null)
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
    public function registerHook(IHookProvider $prov, string $moduleName, ?string $loadBefore = null): void
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
            throw new HookException("Cannot add given class as a hook provider: " . $prov::class);
        }
    }

    /**
     * @param array<string, IHookProvider> $repository
     */
    private function addHook(array &$repository, IHookProvider $module, string $moduleName, ?string $runBeforeModule): bool
    {
        if ($runBeforeModule === null || !array_key_exists($runBeforeModule, $repository)) {
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
    public function runBeforeSystem(): void
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
    public function runBefore(): void
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
    public function runAfter(): void
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
    public function runBeforeView(?array $context = null): void
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
    public function runAfterView(): void
    {
        if ($this->_afterView !== null) {
            foreach ($this->_afterView as $hookProvider) {
                $hookProvider->doAfterView();
            }
        }
    }

    /**
     * @return IAfterHook[]
     */
    public function getAfterController(): array
    {
        return $this->_afterController;
    }

    /**
     * @return IBeforeHook[]
     */
    public function getBeforeController(): array
    {
        return $this->_beforeController;
    }

    /**
     * @return IBeforeSystemHook[]
     */
    public function getBeforeSystem(): array
    {
        return $this->_beforeSystem;
    }

    /**
     * @return IAfterViewHook[]
     */
    public function getAfterView(): array
    {
        return $this->_afterView;
    }

    /**
     * @return IBeforeViewHook[]
     */
    public function getBeforeView(): array
    {
        return $this->_beforeView;
    }
}
