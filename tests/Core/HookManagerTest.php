<?php

use PHPUnit\Framework\TestCase;
use pff\Config;
use pff\Core\HookManager;
use pff\Exception\HookException;
use pff\Iface\IAfterHook;
use pff\Iface\IBeforeHook;
use pff\Iface\IBeforeSystemHook;
use pff\Iface\IHookProvider;

#[\PHPUnit\Framework\Attributes\Group('HookManager')]
class HookManagerTest extends TestCase
{
    private HookManager $object;

    #[\PHPUnit\Framework\Attributes\Before]
    protected function setUp(): void
    {
        $conf = new Config('config.user.php', 'tests/assets');
        $this->object = new HookManager($conf);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function testInitialStateIsValid()
    {
        $this->assertEmpty($this->object->getBeforeController());
        $this->assertEmpty($this->object->getAfterController());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function testRegisterABeforeProvider()
    {
        $beforeHookMock = $this->createMock(IBeforeHook::class);
        $beforeHookMock->method('doBefore')->willReturn('done');

        $this->object->registerHook($beforeHookMock, 'name');

        $beforeControllerHooks = $this->object->getBeforeController();

        $this->assertNotEmpty($beforeControllerHooks);
        $this->assertEmpty($this->object->getAfterController());
        $this->assertEmpty($this->object->getBeforeSystem());
        $this->assertEquals('done', $beforeControllerHooks['name']->doBefore());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function testRegisterAnAfterProvider()
    {
        $afterHookMock = $this->createMock(IAfterHook::class);
        $afterHookMock->method('doAfter')->willReturn('done');

        $this->object->registerHook($afterHookMock, 'name');

        $afterControllerHooks = $this->object->getAfterController();

        $this->assertNotEmpty($afterControllerHooks);
        $this->assertEquals('done', $afterControllerHooks['name']->doAfter());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function testRegisterABeforeSystemProvider()
    {
        $beforeSystemHookMock = $this->createMock(IBeforeSystemHook::class);
        $beforeSystemHookMock->method('doBeforeSystem')->willReturn('done');

        $this->object->registerHook($beforeSystemHookMock, 'name');

        $beforeSystemHooks = $this->object->getBeforeSystem();

        $this->assertNotEmpty($beforeSystemHooks);
        $this->assertEquals('done', $beforeSystemHooks['name']->doBeforeSystem());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function testFailsToRegisterAnEmptyProvider()
    {
        $this->expectException(HookException::class);

        $emptyHookProviderMock = $this->createMock(IHookProvider::class);
        $this->object->registerHook($emptyHookProviderMock, 'name');
    }
}
