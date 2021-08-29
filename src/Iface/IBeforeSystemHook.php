<?php

namespace pff\Iface;

/**
 * Provides hooks to be executes before the system startup
 *
 * @author paolo.fagni<at>gmail.com
 */
interface IBeforeSystemHook extends IHookProvider
{
    /**
     * Executed before the system startup
     *
     * @abstract
     * @return mixed
     */
    public function doBeforeSystem();
}
