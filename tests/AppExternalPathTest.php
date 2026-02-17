<?php

use PHPUnit\Framework\TestCase;
use pff\App;
use pff\Config;
use pff\Core\HelperManager;
use pff\Core\HookManager;
use pff\Core\ModuleManager;

class AppExternalPathTest extends TestCase
{
    #[\PHPUnit\Framework\Attributes\Before]
    protected function setUp(): void
    {
        if (!defined('EXT_ROOT')) {
            define('EXT_ROOT', 'https://example.test/');
        }
    }

    private function createApp(bool $development, string $basePath, string $basePathDev): App
    {
        $config = $this->createMock(Config::class);
        $config->method('getConfigData')->willReturnCallback(static function ($key) use ($development, $basePath, $basePathDev) {
            $map = [
                'development_environment' => $development,
                'base_path' => $basePath,
                'base_path_dev' => $basePathDev,
            ];

            return $map[$key] ?? false;
        });

        return new App(
            $config,
            $this->createMock(HookManager::class),
            $this->createMock(ModuleManager::class),
            $this->createMock(HelperManager::class)
        );
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function getExternalPathUsesDevPathInDevelopment(): void
    {
        $app = $this->createApp(true, 'prod/', 'dev/');

        $this->assertSame('https://example.test/dev/', $app->getExternalPath());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function getExternalPathUsesProductionPathOutsideDevelopment(): void
    {
        $app = $this->createApp(false, 'prod/', 'dev/');

        $this->assertSame('https://example.test/prod/', $app->getExternalPath());
    }
}
