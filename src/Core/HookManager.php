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
class HookManager {

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

    public function __construct(\pff\Config $cfg) {
        $this->_config = $cfg;
    }

    /**
     * Registers a hook provider
     *
     * @param IHookProvider $prov
     * @throws HookException
     */
    public function registerHook(IHookProvider $prov) {
        $found = false;

        if(is_a($prov, '\\pff\\Iface\\IBeforeHook')) {
            $this->_beforeController[] = $prov;
            $found                     = true;
        }

        if(is_a($prov, '\\pff\\Iface\\IAfterHook')) {
            $this->_afterController[] = $prov;
            $found                    = true;
        }

        if(is_a($prov, '\\pff\\Iface\\IBeforeSystemHook')) {
            $this->_beforeSystem[] = $prov;
            $found                 = true;
        }

        if(is_a($prov, '\\pff\\Iface\\IBeforeViewHook')) {
            $this->_beforeView[] = $prov;
            $found               = true;
        }

        if(is_a($prov, '\\pff\\Iface\\IAfterViewHook')) {
            $this->_afterView[] = $prov;
            $found              = true;

        }
        if(!$found) {
            throw new HookException("Cannot add given class as a hook provider: ". get_class($prov));
        }
    }

    /**
     * Executes the registered methods (before the system)
     *
     * @return void
     */
    public function runBeforeSystem() {
        if($this->_beforeSystem !== null) {
            foreach($this->_beforeSystem as $hookProvider) {
                $hookProvider->doBeforeSystem();
            }
        }
    }

    /**
     * Executes the registered methods (before the controller)
     *
     * @return void
     */
    public function runBefore() {
        if($this->_beforeController !== null) {
            foreach($this->_beforeController as $hookProvider) {
                $hookProvider->doBefore();
            }
        }
    }

    /**
     * Executes the registered methods (after the controller)
     *
     * @return void
     */
    public function runAfter() {
        if($this->_afterController !== null) {
            foreach($this->_afterController as $hookProvider) {
                $hookProvider->doAfter();
            }
        }
    }

    /**
     * Executes the registered methods (before the View)
     *
     * @return void
     */
    public function runBeforeView() {
        if($this->_beforeView !== null) {
            foreach($this->_beforeView as $hookProvider) {
                $hookProvider->doBeforeView();
            }
        }
    }

    /**
     * Executes the registered methods (after the View)
     *
     * @return void
     */
    public function runAfterView() {
        if($this->_afterView !== null) {
            foreach($this->_afterView as $hookProvider) {
                $hookProvider->doAfterView();
            }
        }
    }

    /**
     * @return \pff\IAfterHook[]
     */
    public function getAfterController() {
        return $this->_afterController;
    }

    /**
     * @return IBeforeHook[]
     */
    public function getBeforeController() {
        return $this->_beforeController;
    }

    /**
     * @return IBeforeSystemHook[]
     */
    public function getBeforeSystem() {
        return $this->_beforeSystem;
    }

    /**
     * @return IAfterViewHook[]
     */
    public function getAfterView() {
        return $this->_afterView;
    }

    /**
     * @return IBeforeViewHook[]
     */
    public function getBeforeView() {
        return $this->_beforeView;
    }
}