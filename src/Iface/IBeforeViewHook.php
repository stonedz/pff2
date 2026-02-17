<?php

declare(strict_types=1);

namespace pff\Iface;

/**
 * Implements a hook before the registered views
 *
 * @author paolo.fagni<at>gmail.com
 */
interface IBeforeViewHook extends IHookProvider
{
    /**
     * Executes actions before the Views are rendered
     */
    public function doBeforeView(?array $context = null): void;
}
