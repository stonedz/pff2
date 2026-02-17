<?php

use PHPUnit\Framework\TestCase;
use pff\Config;

class ConfigMergeTest extends TestCase
{
    #[\PHPUnit\Framework\Attributes\Test]
    public function loadConfigMergesAdditionalFileValues(): void
    {
        $config = new Config('config.user.php', 'tests/assets');
        $this->assertTrue($config->getConfigData('development_environment'));

        $config->loadConfig('config.merge.php', 'tests/assets');

        $this->assertFalse($config->getConfigData('development_environment'));
        $this->assertSame('merged-value', $config->getConfigData('merged_key'));
    }
}
