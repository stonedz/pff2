<?php

use PHPUnit\Framework\TestCase;

/**
 *
 * @author paolo.fagni<at>gmail.com
 */
class LoggerTest extends TestCase
{
    /**
     * @var \pff\modules\Logger
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp(): void
    {
        $this->object = pff\modules\Logger::getInstance();
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

    public function testInitialState()
    {
        $tmpLoggerArray = $this->object->getLoggers();
        $this->assertIsArray($tmpLoggerArray);
        $this->assertInstanceOf('\\pff\\modules\\Abs\\ALogger', $tmpLoggerArray[0]);
    }

    public function testX()
    {
        $this->expectException('\\pff\\Exception\\ModuleException');
        $this->object->reset();
        $this->object = \pff\modules\Logger::getInstance('nonononon.yaml');
    }

    public function testFail()
    {
        $this->assertTrue(true);
    }
}
