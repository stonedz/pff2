<?php

use PHPUnit\Framework\TestCase;
use pff\App;
use pff\Config;
use pff\Core\HelperManager;
use pff\Core\HookManager;
use pff\Core\ModuleManager;
use pff\Exception\RoutingException;

#[\PHPUnit\Framework\Attributes\Group('App')]
class AppTest extends TestCase
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
        $this->app->setUrl('one/two/three');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function testGetUrl(): void
    {
        $this->assertEquals('one/two/three', $this->app->getUrl());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function testSetRoutes(): void
    {
        $this->app->addRoute('test', 'test');
        $routes = $this->app->getRoutes();

        $this->assertArrayHasKey('test', $routes);
        $this->assertEquals('test', $routes['test']);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function testApplyRouting(): void
    {
        $this->app->addRoute('test', 'test');
        $request = 'test';

        $this->assertTrue($this->app->applyRouting($request));
        $this->assertEquals('Test_Controller', $request);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function testSetRoutesFails(): void
    {
        $this->expectException(RoutingException::class);

        $this->app->addRoute('test', 'testNOTController');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function testSetStaticRoutes(): void
    {
        $this->app->addStaticRoute('test', 'testPage.php');
        $routes = $this->app->getStaticRoutes();

        $this->assertArrayHasKey('test', $routes);
        $this->assertEquals('testPage.php', $routes['test']);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function testApplyStaticRouting(): void
    {
        $this->app->addStaticRoute('test', 'testPage.php');
        $request = 'test';

        $this->assertTrue($this->app->applyStaticRouting($request));
        $this->assertEquals('app' . DS . 'pages' . DS . 'testPage.php', $request);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function testSetStaticRoutesFails(): void
    {
        $this->expectException(RoutingException::class);

        $this->app->addStaticRoute('test', 'testNOTPage.php');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function testApplyStaticRoutesFailsWithInvalidRoute(): void
    {
        $request = 'NO_I_DO_NOT_EXIST';

        $this->assertFalse($this->app->applyStaticRouting($request));
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function testApplyRoutesFailsWithInvalidRoute(): void
    {
        $request = 'NO_I_DO_NOT_EXIST';

        $this->assertFalse($this->app->applyRouting($request));
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function testRunFailsWithInvalidController(): void
    {
        $this->expectException(RoutingException::class);

        $this->app->run();
    }
}
