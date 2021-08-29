<?php

use PHPUnit\Framework\TestCase;

/**
 *
 * @author paolo.fagni<at>gmail.com
 */
class HookManagerTest extends TestCase
{
    /**
     * @var \pff\Core\HookManager
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     *
     */
    protected function setUp(): void
    {
        $conf         = new \pff\Config('config.user.php', 'tests/assets');
        $this->object = new \pff\Core\HookManager($conf);
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

    public function testInitialStateIsValid()
    {
        $this->assertEmpty($this->object->getBeforeController());
        $this->assertEmpty($this->object->getAfterController());
    }

    public function testRegisterABeforeProvider()
    {
        $stub = $this->createMock('\\pff\\Iface\\IBeforeHook');
        $stub->expects($this->any())
             ->method('doBefore')
             ->will($this->returnValue('done'));

        $this->object->registerHook($stub, 'name');

        $listOfHooks = $this->object->getBeforeController();
        $this->assertNotEmpty($listOfHooks);
        $this->assertEmpty($this->object->getAfterController());
        $this->assertEmpty($this->object->getBeforeSystem());
        $this->assertEquals('done', $listOfHooks['name']->doBefore());
    }

    public function testRegisterAnAfterProvider()
    {
        $stub = $this->createMock('\\pff\\Iface\\IAfterHook');
        $stub->expects($this->any())
            ->method('doAfter')
            ->will($this->returnValue('done'));

        $this->object->registerHook($stub, 'name');

        $listOfHooks = $this->object->getAfterController();
        $this->assertNotEmpty($listOfHooks);
        $this->assertEquals('done', $listOfHooks['name']->doAfter());
    }

    public function testRegisterABeforeSystemProvider()
    {
        $stub = $this->createMock('\\pff\\Iface\\IBeforeSystemHook');
        $stub->expects($this->any())
            ->method('doBeforeSystem')
            ->will($this->returnValue('done'));

        $this->object->registerHook($stub, 'name');

        $listOfHooks = $this->object->getBeforeSystem();
        $this->assertNotEmpty($listOfHooks);
        $this->assertEquals('done', $listOfHooks['name']->doBeforeSystem());
    }

    public function testFailsToRegisterAnEmptyProvider()
    {
        $this->expectException('\\pff\\Exception\\HookException');
        $stub = $this->createMock('\\pff\\Iface\\IHookProvider');
        $this->object->registerHook($stub, 'name');
    }
}
