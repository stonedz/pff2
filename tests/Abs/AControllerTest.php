<?php

namespace pff\controllers {
    class Lifecycle_Controller extends \pff\Abs\AController
    {
        public static array $log = [];

        public function index()
        {
            self::$log[] = 'index';
        }

        public function show()
        {
            self::$log[] = 'show';
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
    use pff\Exception\PffException;

    class AControllerTest extends TestCase
    {
        private function createController(string $action = 'show', array $params = []): \pff\controllers\Lifecycle_Controller
        {
            $config = $this->createMock(Config::class);
            $config->method('getConfigData')->willReturnCallback(static function ($key) {
                if ($key === 'orm') {
                    return false;
                }
                return false;
            });

            $hookManager = $this->createMock(HookManager::class);
            $moduleManager = $this->createMock(ModuleManager::class);
            $helperManager = $this->createMock(HelperManager::class);

            $app = new App($config, $hookManager, $moduleManager, $helperManager);

            return new \pff\controllers\Lifecycle_Controller('Lifecycle_Controller', $app, $action, $params);
        }

        #[\PHPUnit\Framework\Attributes\Test]
        public function getParamReturnsValueByNameAndIndex(): void
        {
            $controller = $this->createController('show', ['id' => 'abc', 0 => 'first']);

            $this->assertSame('abc', $controller->getParam('id'));
            $this->assertSame('first', $controller->getParam(0));
        }

        #[\PHPUnit\Framework\Attributes\Test]
        public function getParamThrowsOnMissingValue(): void
        {
            $controller = $this->createController('show', []);

            $this->expectException(PffException::class);
            $controller->getParam('missing');
        }

        #[\PHPUnit\Framework\Attributes\Test]
        public function beforeAndAfterFiltersRunForCurrentAction(): void
        {
            $controller = $this->createController('show', []);
            $executionOrder = [];

            $controller->registerBeforeFilter('show', static function () use (&$executionOrder) {
                $executionOrder[] = 'before';
            });

            $controller->registerAfterFilter('show', static function () use (&$executionOrder) {
                $executionOrder[] = 'after';
            });

            $controller->beforeFilter();
            $controller->afterFilter();

            $this->assertSame(['before', 'after'], $executionOrder);
        }

        #[\PHPUnit\Framework\Attributes\Test]
        public function getLayoutThrowsWhenNotSet(): void
        {
            $controller = $this->createController();

            $this->expectException(PffException::class);
            $controller->getLayout();
        }

        #[\PHPUnit\Framework\Attributes\Test]
        public function setLayoutResetsExistingViewsAndPlacesLayoutAsFirstView(): void
        {
            $controller = $this->createController();

            $existingView = $this->getMockBuilder(\pff\Abs\AView::class)
                ->disableOriginalConstructor()
                ->onlyMethods(['set', 'render', 'renderHtml'])
                ->getMock();

            $layoutView = $this->getMockBuilder(\pff\Abs\AView::class)
                ->disableOriginalConstructor()
                ->onlyMethods(['set', 'render', 'renderHtml'])
                ->getMock();

            $controller->addView($existingView);
            $controller->setLayout($layoutView);

            $views = $controller->getViews();
            $this->assertCount(1, $views);
            $this->assertSame($layoutView, $views[0]);
            $this->assertSame($layoutView, $controller->getLayout());
        }

        #[\PHPUnit\Framework\Attributes\Test]
        public function setIsRenderActionToggleIsReadable(): void
        {
            $controller = $this->createController();

            $controller->setIsRenderAction(true);
            $this->assertTrue($controller->getIsRenderAction());

            $controller->setIsRenderAction(false);
            $this->assertFalse($controller->getIsRenderAction());
        }
    }
}
