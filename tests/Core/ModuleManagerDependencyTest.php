<?php

use PHPUnit\Framework\TestCase;
use pff\Core\HookManager;
use pff\Core\ModuleManager;
use pff\Core\ServiceContainer;
use Symfony\Component\Yaml\Parser;

class ModuleManagerDependencyTest extends TestCase
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
                'modules' => [],
            ];

            return $map[$key] ?? false;
        });

        ServiceContainer::set()['config'] = $config;
        ServiceContainer::set()['yamlparser'] = new Parser();
        ServiceContainer::set()['app'] = new stdClass();
        ServiceContainer::set()['hookmanager'] = new HookManager($config);

        $modulesProperty = new ReflectionProperty(ModuleManager::class, '_modules');
        $modulesProperty->setValue(null, []);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function loadingExceptionHandlerAlsoLoadsSessionDependency(): void
    {
        ModuleManager::loadModule('exception_handler');

        $this->assertTrue(ModuleManager::isLoaded('exception_handler'));
        $this->assertTrue(ModuleManager::isLoaded('session'));
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function loadingExceptionHandlerRegistersHooksForDependencyAndModule(): void
    {
        $config = ServiceContainer::get('config');
        $hookManager = $this->createMock(HookManager::class);
        $hookManager->expects($this->exactly(2))->method('registerHook');

        ServiceContainer::set()['hookmanager'] = $hookManager;
        ServiceContainer::set()['config'] = $config;
        ServiceContainer::set()['yamlparser'] = new Parser();
        ServiceContainer::set()['app'] = new stdClass();

        $modulesProperty = new ReflectionProperty(ModuleManager::class, '_modules');
        $modulesProperty->setValue(null, []);

        ModuleManager::loadModule('exception_handler');

        $this->assertTrue(ModuleManager::isLoaded('exception_handler'));
        $this->assertTrue(ModuleManager::isLoaded('session'));
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function getModuleLoadsOnDemandWhenNotPreviouslyLoaded(): void
    {
        ServiceContainer::set()['hookmanager'] = new HookManager(ServiceContainer::get('config'));

        $manager = new ModuleManager();
        $module = $manager->getModule('url');

        $this->assertInstanceOf(\pff\modules\Url::class, $module);
        $this->assertTrue(ModuleManager::isLoaded('url'));
    }
}
