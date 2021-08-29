<?php

namespace pff\Iface;

/**
 * Implements a hook after the registered views
 *
 * @author paolo.fagni<at>gmail.com
 */
interface IAfterViewHook extends IHookProvider
{
    /**
     * Executes actions after the views are rendered
     *
     * @abstract
     * @return mixed
     */
    public function doAfterView();
}
