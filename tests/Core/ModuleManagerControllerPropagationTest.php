<?php

use PHPUnit\Framework\TestCase;
use pff\Core\HookManager;
use pff\Core\ModuleManager;
use pff\Core\ServiceContainer;
use Symfony\Component\Yaml\Parser;

class ModuleManagerControllerPropagationTest extends TestCase
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
                'modules' => ['url', 'session'],
            ];

            return $map[$key] ?? false;
        });

        ServiceContainer::set()['config'] = $config;
        ServiceContainer::set()['yamlparser'] = new Parser();
        ServiceContainer::set()['hookmanager'] = new HookManager($config);
        $appMock = $this->createMock(\pff\App::class);
        ServiceContainer::set()['app'] = $appMock;

        $modulesProperty = new ReflectionProperty(ModuleManager::class, '_modules');
        $modulesProperty->setValue(null, []);

        ModuleManager::initModules();
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function setControllerPropagatesControllerToAllLoadedModules(): void
    {
        $controller = $this->getMockBuilder(\pff\Abs\AController::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['index'])
            ->getMock();

        $outputProperty = new ReflectionProperty(\pff\Abs\AController::class, '_output');
        $outputProperty->setValue($controller, new \pff\Core\Outputs\HTMLOut());

        $viewsProperty = new ReflectionProperty(\pff\Abs\AController::class, '_view');
        $viewsProperty->setValue($controller, []);

        $manager = new ModuleManager();
        $manager->setController($controller);

        $modulesProperty = new ReflectionProperty(ModuleManager::class, '_modules');
        $modules = (array) $modulesProperty->getValue();

        $this->assertNotEmpty($modules);
        foreach ($modules as $module) {
            $this->assertSame($controller, $module->getController());
        }
    }
}
