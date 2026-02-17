<?php

declare(strict_types=1);

namespace pff\Iface;

/**
 * @author paolo.fagni<at>gmail.com
 */
interface IConfigurableModule
{
    /**
     * @param array<string, mixed> $parsedConfig
     */
    public function loadConfig(array $parsedConfig): void;
}
