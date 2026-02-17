<?php

use PHPUnit\Framework\TestCase;

/**
 * LoggerFile test class
 *
 * @author paolo.fagni<at>gmail.com
 */
class LoggerFileTest extends TestCase
{
    /**
     * @var \pff\modules\Utils\LoggerFile
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp(): void
    {
        $this->object = new pff\modules\Utils\LoggerFile();
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

    public function testInitialStateIsOk(): void
    {
        $this->assertNull($this->object->getFp());
    }

    /*public function testFilePointer() {
        $this->assertInternalType('resource', $this->object->getLogFile());

    }*/
}
