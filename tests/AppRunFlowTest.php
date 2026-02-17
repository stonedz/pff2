<?php

namespace pff\controllers {
    class Runtime_Controller extends \pff\Abs\AController
    {
        public static array $called = [];

        public function index()
        {
            self::$called[] = 'index';
        }

        public function show()
        {
            self::$called[] = 'show';
        }
    }
}

namespace {
    use PHPUnit\Framework\TestCase;
    use pff\App;
    use pff\Config;
    use pff\Core\HelperManager;
    use pff\Core\HookManager;
    use pff\Core\ModuleManager;
    use pff\Exception\RoutingException;
    use pff\controllers\Runtime_Controller;

    #[\PHPUnit\Framework\Attributes\Group('App')]
    class AppRunFlowTest extends TestCase
    {
        private string $runtimeControllerFile;
        private string $staticPageFileTests;
        private string $staticPageFileApp;

        #[\PHPUnit\Framework\Attributes\Before]
        protected function setUp(): void
        {
            $this->runtimeControllerFile = CONTROLLERS . DS . 'Runtime_Controller.php';
            if (!is_dir(CONTROLLERS)) {
                mkdir(CONTROLLERS, 0775, true);
            }
            file_put_contents($this->runtimeControllerFile, "<?php\n");

            $staticContent = "<?php echo 'STATIC_OK';";
            $this->staticPageFileTests = PAGES . DS . 'pff_static_test_page.php';
            if (!is_dir(PAGES)) {
                mkdir(PAGES, 0775, true);
            }
            file_put_contents($this->staticPageFileTests, $staticContent);

            $this->staticPageFileApp = ROOT . DS . 'app' . DS . 'pages' . DS . 'pff_static_test_page.php';
            if (!is_dir(dirname($this->staticPageFileApp))) {
                mkdir(dirname($this->staticPageFileApp), 0775, true);
            }
            file_put_contents($this->staticPageFileApp, $staticContent);

            Runtime_Controller::$called = [];
        }

        #[\PHPUnit\Framework\Attributes\After]
        protected function tearDown(): void
        {
            if (file_exists($this->runtimeControllerFile)) {
                unlink($this->runtimeControllerFile);
            }
            if (file_exists($this->staticPageFileTests)) {
                unlink($this->staticPageFileTests);
            }
            if (file_exists($this->staticPageFileApp)) {
                unlink($this->staticPageFileApp);
            }
        }

        /**
         * @return array{0: App, 1: HookManager&\PHPUnit\Framework\MockObject\MockObject, 2: ModuleManager&\PHPUnit\Framework\MockObject\MockObject}
         */
        private function createApp(): array
        {
            $config = $this->createMock(Config::class);
            $hookManager = $this->createMock(HookManager::class);
            $moduleManager = $this->createMock(ModuleManager::class);
            $helperManager = $this->createMock(HelperManager::class);

            $config->method('getConfigData')->willReturnCallback(static function ($key) {
                $map = [
                    'orm' => false,
                    'development_environment' => true,
                    'show_all_errors' => false,
                    'base_path' => '',
                    'base_path_dev' => '',
                ];

                return $map[$key] ?? false;
            });

            return [new App($config, $hookManager, $moduleManager, $helperManager), $hookManager, $moduleManager];
        }

        #[\PHPUnit\Framework\Attributes\Test]
        public function runExecutesMappedControllerAction(): void
        {
            [$app, $hookManager, $moduleManager] = $this->createApp();

            $hookManager->expects($this->once())->method('runBeforeSystem');
            $hookManager->expects($this->once())->method('runBefore');
            $hookManager->expects($this->once())->method('runAfter');
            $moduleManager->expects($this->once())->method('setController');

            $app->addRoute('runtime', 'runtime/show');
            $app->setUrl('runtime');
            $app->run();

            $this->assertSame(['show'], Runtime_Controller::$called);
        }

        #[\PHPUnit\Framework\Attributes\Test]
        public function runThrowsWhenMappedActionDoesNotExist(): void
        {
            [$app, $hookManager, $moduleManager] = $this->createApp();

            $hookManager->expects($this->once())->method('runBeforeSystem');
            $hookManager->expects($this->once())->method('runBefore');
            $hookManager->expects($this->never())->method('runAfter');
            $moduleManager->expects($this->once())->method('setController');

            $app->addRoute('runtime', 'runtime/missingAction');
            $app->setUrl('runtime');

            $startLevel = ob_get_level();
            try {
                $this->expectException(RoutingException::class);
                $app->run();
            } finally {
                while (ob_get_level() > $startLevel) {
                    ob_end_clean();
                }
            }
        }

        #[\PHPUnit\Framework\Attributes\Test]
        public function runIncludesStaticRoutePage(): void
        {
            [$app, $hookManager, $moduleManager] = $this->createApp();

            $hookManager->expects($this->once())->method('runBeforeSystem');
            $hookManager->expects($this->once())->method('runBefore');
            $hookManager->expects($this->once())->method('runAfter');
            $moduleManager->expects($this->never())->method('setController');

            $app->addStaticRoute('pff-static', 'pff_static_test_page.php');
            $app->setUrl('pff-static');

            ob_start();
            $app->run();
            $output = (string) ob_get_clean();

            $this->assertStringContainsString('STATIC_OK', $output);
        }
    }
}
