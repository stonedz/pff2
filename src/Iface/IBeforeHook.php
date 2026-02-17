<?php

declare(strict_types=1);

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
     */
    public function doBefore(): void;
}
