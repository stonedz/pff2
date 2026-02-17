<?php

declare(strict_types=1);

namespace pff\Iface;

/**
 * Provides hooks to be executed before the system startup
 *
 * @author paolo.fagni<at>gmail.com
 */
interface IBeforeSystemHook extends IHookProvider
{
    /**
     * Executed before the system startup
     */
    public function doBeforeSystem(): void;
}
