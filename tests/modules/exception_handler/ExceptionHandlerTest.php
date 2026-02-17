<?php

use PHPUnit\Framework\TestCase;
use pff\App;
use pff\Config;
use pff\Core\HelperManager;
use pff\Core\HookManager;
use pff\Core\ModuleManager;
use pff\Core\ServiceContainer;

class ExceptionHandlerTest extends TestCase
{
    private function createAppWithConfig(Config $config): App
    {
        $hookManager = $this->createMock(HookManager::class);
        $moduleManager = $this->createMock(ModuleManager::class);
        $helperManager = $this->createMock(HelperManager::class);

        return new App($config, $hookManager, $moduleManager, $helperManager);
    }

    #[\PHPUnit\Framework\Attributes\Before]
    protected function setUp(): void
    {
        if (!defined('EXT_ROOT')) {
            define('EXT_ROOT', '/');
        }

        ServiceContainer::$pimple = null;
        ServiceContainer::initPimple();
        $config = $this->createMock(Config::class);
        $config->method('getConfigData')->willReturn(false);
        ServiceContainer::set()['app'] = $this->createAppWithConfig($config);
        ServiceContainer::set()['modulemanager'] = new class {
            public function isLoaded($name)
            {
                return false;
            }
        };
    }

    #[\PHPUnit\Framework\Attributes\Test]
    #[\PHPUnit\Framework\Attributes\RunInSeparateProcess]
    public function productionHidesExceptionDetailsByDefault(): void
    {
        ServiceContainer::$pimple = null;
        ServiceContainer::initPimple();
        ServiceContainer::set()['modulemanager'] = new class {
            public function isLoaded($name)
            {
                return false;
            }
        };

        $config = $this->createMock(Config::class);
        $config->method('getConfigData')->willReturnCallback(static function ($key) {
            $map = [
                'development_environment' => false,
                'show_exception_details' => false,
            ];
            return $map[$key] ?? false;
        });

        $app = $this->createAppWithConfig($config);
        ServiceContainer::set()['app'] = $app;

        $handler = new \pff\modules\ExceptionHandler();
        $handler->setConfig($config);
        $handler->setApp($app);

        $startLevel = ob_get_level();
        ob_start();
        try {
            $handler->manageExceptions(new RuntimeException('TOP-SECRET-ERROR', 500));
            $output = (string) ob_get_clean();
        } finally {
            while (ob_get_level() > $startLevel) {
                ob_end_clean();
            }
        }

        $this->assertStringNotContainsString('TOP-SECRET-ERROR', $output);
        $this->assertStringContainsString('An unexpected error occurred.', $output);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    #[\PHPUnit\Framework\Attributes\RunInSeparateProcess]
    public function developmentCanShowExceptionDetails(): void
    {
        ServiceContainer::$pimple = null;
        ServiceContainer::initPimple();
        ServiceContainer::set()['modulemanager'] = new class {
            public function isLoaded($name)
            {
                return false;
            }
        };

        $config = $this->createMock(Config::class);
        $config->method('getConfigData')->willReturnCallback(static function ($key) {
            $map = [
                'development_environment' => true,
                'show_exception_details' => true,
            ];
            return $map[$key] ?? false;
        });

        $app = $this->createAppWithConfig($config);
        ServiceContainer::set()['app'] = $app;

        $handler = new \pff\modules\ExceptionHandler();
        $handler->setConfig($config);
        $handler->setApp($app);

        $startLevel = ob_get_level();
        ob_start();
        try {
            $handler->manageExceptions(new RuntimeException('VISIBLE-ERROR', 500));
            $output = (string) ob_get_clean();
        } finally {
            while (ob_get_level() > $startLevel) {
                ob_end_clean();
            }
        }

        $this->assertStringContainsString('VISIBLE-ERROR', $output);
    }
}
