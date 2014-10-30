<?php

namespace pff\Iface;

/**
 * Implements a hook after the controller
 *
 * @author paolo.fagni<at>gmail.com
 */
interface IAfterHook extends IHookProvider  {

    /**
     * Executes actios after the controller has finished its work.
     *
     * @abstract
     * @return mixed
     */
    public function doAfter();
}
