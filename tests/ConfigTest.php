<?php

use PHPUnit\Framework\TestCase;

/**
 * Test for Config
 * @author paolo.fagni<at>gmail.com
 */
class ConfigTest extends TestCase
{
    /**
     * @var \pff\Config
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp(): void
    {
        $this->object = new \pff\Config('config.user.php', 'tests/assets');
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     *
     * @return void
     */
    protected function tearDown(): void
    {
    }

    public function testConfigurationIsNotEmptyAtStartup()
    {
        $this->assertNotEmpty($this->object->getConfig());
    }

    public function testGetConfigFailsWithInvalid()
    {
        $this->expectException('\\pff\\Exception\\ConfigException');
        $this->object->getConfigData('NoIDoNotExistxxxxx');
    }

    public function testGetAconfigurationValue()
    {
        $this->assertIsBool($this->object->getConfigData('development_environment'));
    }

    public function testSetAConfigurationValue()
    {
        $this->object->setConfig('aTestValue', 12);
        $this->assertEquals($this->object->getConfigData('aTestValue'), 12);
    }

    public function testSetAConfigurationFailsWithoutAString()
    {
        $this->expectException('\\pff\\Exception\\ConfigException');
        $this->object->setConfig(array(), 12);
    }

    public function testGetConfigReturnsArrayWithNoParamaters()
    {
        //$this->assertTrue(is_array($this->object->getConfig()));
        $this->assertIsArray($this->object->getConfigData());
    }

    public function testLoadConfigFailsWithInexistantFile()
    {
        $this->expectException('\\pff\\Exception\\ConfigException');
        $this->object->loadConfig('nonono', 'config');
    }
}
