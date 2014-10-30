<?php

namespace pff\Interface;

/**
 * Before controller hook
 *
 * @author paolo.fagni<at>gmail.com
 */
interface IBeforeHook extends \pff\IHookProvider {

    /**
     * Executes actions before the Controller
     *
     * @abstract
     * @return mixed
     */
    public function doBefore();
}
