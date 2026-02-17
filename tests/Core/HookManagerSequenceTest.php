<?php

use PHPUnit\Framework\TestCase;
use pff\Config;
use pff\Core\HookManager;
use pff\Iface\IAfterHook;
use pff\Iface\IBeforeHook;

class HookManagerSequenceTest extends TestCase
{
    private HookManager $hookManager;

    #[\PHPUnit\Framework\Attributes\Before]
    protected function setUp(): void
    {
        $config = new Config('config.user.php', 'tests/assets');
        $this->hookManager = new HookManager($config);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function runBeforeExecutesHooksInRegistrationOrderWithoutLoadBefore(): void
    {
        $sequence = [];

        $before1 = $this->createMock(IBeforeHook::class);
        $before1->method('doBefore')->willReturnCallback(function () use (&$sequence) {
            $sequence[] = 'first';
        });

        $before2 = $this->createMock(IBeforeHook::class);
        $before2->method('doBefore')->willReturnCallback(function () use (&$sequence) {
            $sequence[] = 'second';
        });

        $this->hookManager->registerHook($before1, 'first');
        $this->hookManager->registerHook($before2, 'second');

        $this->hookManager->runBefore();

        $this->assertSame(['first', 'second'], $sequence);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function runAfterExecutesAllRegisteredAfterHooks(): void
    {
        $after1 = $this->createMock(IAfterHook::class);
        $after1->expects($this->once())->method('doAfter');

        $after2 = $this->createMock(IAfterHook::class);
        $after2->expects($this->once())->method('doAfter');

        $this->hookManager->registerHook($after1, 'after1');
        $this->hookManager->registerHook($after2, 'after2');

        $this->hookManager->runAfter();
    }
}
