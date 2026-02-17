<?php

use PHPUnit\Framework\TestCase;
use pff\App;
use pff\Config;
use pff\Core\HelperManager;
use pff\Core\HookManager;
use pff\Core\ModuleManager;

class AppRoutingDetailsTest extends TestCase
{
    private App $app;

    #[\PHPUnit\Framework\Attributes\Before]
    protected function setUp(): void
    {
        $config = $this->createMock(Config::class);
        $hookManager = $this->createMock(HookManager::class);
        $moduleManager = $this->createMock(ModuleManager::class);
        $helperManager = $this->createMock(HelperManager::class);

        $this->app = new App($config, $hookManager, $moduleManager, $helperManager);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function applyRoutingSetsControllerActionAndPrependsRouteParams(): void
    {
        $this->app->addRoute('custom', 'test/show/fixedA/fixedB');

        $request = 'custom';
        $action = null;
        $urlParams = ['dynamicA', 'dynamicB'];

        $result = $this->app->applyRouting($request, $action, $urlParams);

        $this->assertTrue($result);
        $this->assertSame('Test_Controller', $request);
        $this->assertSame('show', $action);
        $this->assertSame(['fixedA', 'fixedB', 'dynamicA', 'dynamicB'], $urlParams);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function applyRoutingMatchesRequestCaseInsensitively(): void
    {
        $this->app->addRoute('mixedcase', 'test/index');

        $request = 'MIXEDCASE';
        $result = $this->app->applyRouting($request);

        $this->assertTrue($result);
        $this->assertSame('Test_Controller', $request);
    }
}
