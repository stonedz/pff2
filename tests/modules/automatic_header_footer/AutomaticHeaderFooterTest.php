<?php

use PHPUnit\Framework\TestCase;

/**
 *
 * @author paolo.fagni<at>gmail.com
 */
class AutomaticHeaderFooterTest extends TestCase
{
    /**
     * @var \pff\modules\AutomaticHeaderFooter
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp(): void
    {
        $this->object = new \pff\modules\AutomaticHeaderFooter();
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

    public function testReadConfigFile(): void
    {
        $conf = $this->object->readConfig('automatic_header_footer/module.conf.yaml');
        $this->assertArrayHasKey('moduleConf', $conf);
    }

    public function testReadConfigFailsWithInvalidFile(): void
    {
        $this->expectException('\\pff\\Exception\\ModuleException');
        $this->object->readConfig('automatic_header_footer/i_do_not_exist_and_never_will.conf.jdjd');
    }
}
