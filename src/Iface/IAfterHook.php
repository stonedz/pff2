<?php

declare(strict_types=1);

namespace pff\Iface;

/**
 * Implements a hook after the controller
 *
 * @author paolo.fagni<at>gmail.com
 */
interface IAfterHook extends IHookProvider
{
    /**
     * Executes actions after the controller has finished its work.
     */
    public function doAfter(): void;
}
