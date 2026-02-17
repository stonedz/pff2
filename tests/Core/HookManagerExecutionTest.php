<?php

use PHPUnit\Framework\TestCase;
use pff\Config;
use pff\Core\HookManager;
use pff\Iface\IAfterViewHook;
use pff\Iface\IBeforeHook;
use pff\Iface\IBeforeViewHook;

#[\PHPUnit\Framework\Attributes\Group('HookManager')]
class HookManagerExecutionTest extends TestCase
{
    private HookManager $hookManager;

    #[\PHPUnit\Framework\Attributes\Before]
    protected function setUp(): void
    {
        $conf = new Config('config.user.php', 'tests/assets');
        $this->hookManager = new HookManager($conf);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function runBeforeViewAndAfterViewInvokeRegisteredHooks(): void
    {
        $beforeView = $this->createMock(IBeforeViewHook::class);
        $beforeView->expects($this->once())
            ->method('doBeforeView')
            ->with(['controller' => 'x']);

        $afterView = $this->createMock(IAfterViewHook::class);
        $afterView->expects($this->once())
            ->method('doAfterView');

        $this->hookManager->registerHook($beforeView, 'beforeView');
        $this->hookManager->registerHook($afterView, 'afterView');

        $this->hookManager->runBeforeView(['controller' => 'x']);
        $this->hookManager->runAfterView();
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function registerHookRunBeforePlacesProviderInRequestedOrder(): void
    {
        $first = $this->createMock(IBeforeHook::class);
        $second = $this->createMock(IBeforeHook::class);

        $this->hookManager->registerHook($first, 'first');
        $this->hookManager->registerHook($second, 'second', 'first');

        $keys = array_keys($this->hookManager->getBeforeController());
        $this->assertSame(['second', 'first'], $keys);
    }
}
