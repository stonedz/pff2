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
    public function testInitialStateIsValid(): void
    {
        $this->assertEmpty($this->object->getBeforeController());
        $this->assertEmpty($this->object->getAfterController());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function testRegisterABeforeProvider(): void
    {
        $beforeHookMock = $this->createMock(IBeforeHook::class);

        $this->object->registerHook($beforeHookMock, 'name');

        $beforeControllerHooks = $this->object->getBeforeController();

        $this->assertNotEmpty($beforeControllerHooks);
        $this->assertEmpty($this->object->getAfterController());
        $this->assertEmpty($this->object->getBeforeSystem());
        $this->assertSame($beforeHookMock, $beforeControllerHooks['name']);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function testRegisterAnAfterProvider(): void
    {
        $afterHookMock = $this->createMock(IAfterHook::class);

        $this->object->registerHook($afterHookMock, 'name');

        $afterControllerHooks = $this->object->getAfterController();

        $this->assertNotEmpty($afterControllerHooks);
        $this->assertSame($afterHookMock, $afterControllerHooks['name']);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function testRegisterABeforeSystemProvider(): void
    {
        $beforeSystemHookMock = $this->createMock(IBeforeSystemHook::class);

        $this->object->registerHook($beforeSystemHookMock, 'name');

        $beforeSystemHooks = $this->object->getBeforeSystem();

        $this->assertNotEmpty($beforeSystemHooks);
        $this->assertSame($beforeSystemHookMock, $beforeSystemHooks['name']);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function testFailsToRegisterAnEmptyProvider(): void
    {
        $this->expectException(HookException::class);

        $emptyHookProviderMock = $this->createMock(IHookProvider::class);
        $this->object->registerHook($emptyHookProviderMock, 'name');
    }
}
