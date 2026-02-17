<?php

use PHPUnit\Framework\TestCase;
use pff\Core\HookManager;
use pff\Core\ModuleManager;
use pff\Core\ServiceContainer;
use pff\Exception\ModuleException;
use Symfony\Component\Yaml\Parser;

class ModuleManagerTest extends TestCase
{
    #[\PHPUnit\Framework\Attributes\Before]
    protected function setUp(): void
    {
        ServiceContainer::$pimple = null;
        ServiceContainer::initPimple();

        $config = $this->createMock(\pff\Config::class);
        $config->method('getConfigData')->willReturnCallback(static function ($key) {
            $map = [
                'app_name' => 'pff-test',
                'modules' => ['url'],
            ];

            return $map[$key] ?? false;
        });

        ServiceContainer::set()['config'] = $config;
        ServiceContainer::set()['yamlparser'] = new Parser();
        ServiceContainer::set()['hookmanager'] = new HookManager($config);
        ServiceContainer::set()['app'] = new stdClass();

        $modulesProperty = new ReflectionProperty(ModuleManager::class, '_modules');
        $modulesProperty->setValue(null, []);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function loadModuleLoadsBuiltinUrlModule(): void
    {
        $module = ModuleManager::loadModule('url');

        $this->assertInstanceOf(\pff\modules\Url::class, $module);
        $this->assertTrue(ModuleManager::isLoaded('url'));
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function initModulesLoadsConfiguredModules(): void
    {
        ModuleManager::initModules();

        $this->assertTrue(ModuleManager::isLoaded('url'));
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function loadModuleThrowsForUnknownModule(): void
    {
        $this->expectException(ModuleException::class);
        ModuleManager::loadModule('module_that_does_not_exist');
    }
}
