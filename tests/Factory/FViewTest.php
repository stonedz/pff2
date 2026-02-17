<?php

use PHPUnit\Framework\TestCase;
use pff\Core\ServiceContainer;
use pff\Exception\ViewException;
use pff\Factory\FView;

class FViewTest extends TestCase
{
    private string $templatePath;

    #[\PHPUnit\Framework\Attributes\Before]
    protected function setUp(): void
    {
        ServiceContainer::$pimple = null;
        ServiceContainer::initPimple();

        $appMock = $this->createMock(\pff\App::class);
        $appMock->method('getExternalPath')->willReturn('/');
        ServiceContainer::set()['app'] = $appMock;

        ServiceContainer::set()['modulemanager'] = new class {
            public function isLoaded($name)
            {
                return false;
            }
        };

        $this->templatePath = ROOT . DS . 'app' . DS . 'views' . DS . 'fview_test.php';
        if (!is_dir(dirname($this->templatePath))) {
            mkdir(dirname($this->templatePath), 0775, true);
        }
        file_put_contents($this->templatePath, 'fview test');
    }

    #[\PHPUnit\Framework\Attributes\After]
    protected function tearDown(): void
    {
        if (file_exists($this->templatePath)) {
            unlink($this->templatePath);
        }
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function createReturnsPhpViewForPhpTemplate(): void
    {
        $view = FView::create('fview_test.php');

        $this->assertInstanceOf(\pff\Core\ViewPHP::class, $view);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function createFallsBackToPhpViewForUnknownType(): void
    {
        $view = FView::create('fview_test.php', null, 'unknown');

        $this->assertInstanceOf(\pff\Core\ViewPHP::class, $view);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function createThrowsWhenTemplateDoesNotExist(): void
    {
        $this->expectException(ViewException::class);

        FView::create('fview_missing_template.php');
    }
}
