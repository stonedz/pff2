<?php

use PHPUnit\Framework\TestCase;
use pff\Config;
use pff\Exception\ConfigException;

/**
 * Test for Config
 * @author paolo.fagni<at>gmail.com
 */
class ConfigTest extends TestCase
{
    private Config $config;

    #[\PHPUnit\Framework\Attributes\Before]
    public function setUp(): void
    {
        $this->config = new Config('config.user.php', 'tests/assets');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function configurationIsNotEmptyAtStartup(): void
    {
        $this->assertNotEmpty($this->config->getConfig());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function getConfigFailsWithInvalid(): void
    {
        $this->assertFalse($this->config->getConfigData('NoIDoNotExistxxxxx'));
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function getAconfigurationValue(): void
    {
        $this->assertIsBool($this->config->getConfigData('development_environment'));
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function setAConfigurationValue(): void
    {
        $this->config->setConfig('aTestValue', 12);
        $this->assertEquals($this->config->getConfigData('aTestValue'), 12);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function setAConfigurationFailsWithoutAString(): void
    {
        $this->expectException(\TypeError::class);
        $this->config->setConfig([], 12);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function getConfigReturnsArrayWithNoParamaters(): void
    {
        $this->assertIsArray($this->config->getConfigData());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function loadConfigFailsWithInexistantFile(): void
    {
        $this->expectException(ConfigException::class);
        $this->config->loadConfig('nonono', 'config');
    }
}
