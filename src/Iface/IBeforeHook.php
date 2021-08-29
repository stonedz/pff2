<?php

namespace pff\Iface;

/**
 * Before controller hook
 *
 * @author paolo.fagni<at>gmail.com
 */
interface IBeforeHook extends IHookProvider
{
    /**
     * Executes actions before the Controller
     *
     * @abstract
     * @return mixed
     */
    public function doBefore();
}
