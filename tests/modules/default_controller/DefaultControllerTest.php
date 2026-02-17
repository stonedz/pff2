<?php

use PHPUnit\Framework\TestCase;
use pff\App;

class DefaultControllerTest extends TestCase
{
    private string $controllerFile;
    private string $pageFile;

    #[\PHPUnit\Framework\Attributes\Before]
    protected function setUp(): void
    {
        $this->controllerFile = ROOT . DS . 'app' . DS . 'controllers' . DS . 'PffTmp_Controller.php';
        $this->pageFile = ROOT . DS . 'app' . DS . 'pages' . DS . 'pff_tmp_page.php';

        if (!is_dir(dirname($this->controllerFile))) {
            mkdir(dirname($this->controllerFile), 0775, true);
        }
        if (!is_dir(dirname($this->pageFile))) {
            mkdir(dirname($this->pageFile), 0775, true);
        }
    }

    #[\PHPUnit\Framework\Attributes\After]
    protected function tearDown(): void
    {
        if (file_exists($this->controllerFile)) {
            unlink($this->controllerFile);
        }
        if (file_exists($this->pageFile)) {
            unlink($this->pageFile);
        }
    }

    private function setDefaultController(\pff\modules\DefaultController $module, string $name): void
    {
        $property = new ReflectionProperty(\pff\modules\DefaultController::class, '_defaultController');
        $property->setValue($module, $name);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function doBeforeSystemRewritesUnknownControllerToDefaultController(): void
    {
        $app = $this->createMock(App::class);
        $app->method('getUrl')->willReturn('unknown/path');
        $app->expects($this->once())->method('setUrl')->with('index/unknown/path');

        $module = (new ReflectionClass(\pff\modules\DefaultController::class))->newInstanceWithoutConstructor();
        $this->setDefaultController($module, 'index');
        $module->setApp($app);

        $module->doBeforeSystem();
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function doBeforeSystemDoesNothingWhenControllerExists(): void
    {
        file_put_contents($this->controllerFile, "<?php\n");

        $app = $this->createMock(App::class);
        $app->method('getUrl')->willReturn('pffTmp/list');
        $app->expects($this->never())->method('setUrl');

        $module = (new ReflectionClass(\pff\modules\DefaultController::class))->newInstanceWithoutConstructor();
        $this->setDefaultController($module, 'index');
        $module->setApp($app);

        $module->doBeforeSystem();
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function doBeforeSystemDoesNothingWhenStaticPageExists(): void
    {
        file_put_contents($this->pageFile, "<?php\n");

        $app = $this->createMock(App::class);
        $app->method('getUrl')->willReturn('pff_tmp_page.php');
        $app->expects($this->never())->method('setUrl');

        $module = (new ReflectionClass(\pff\modules\DefaultController::class))->newInstanceWithoutConstructor();
        $this->setDefaultController($module, 'index');
        $module->setApp($app);

        $module->doBeforeSystem();
    }
}
