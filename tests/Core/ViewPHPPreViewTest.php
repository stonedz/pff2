<?php

use PHPUnit\Framework\TestCase;
use pff\Core\ServiceContainer;
use pff\Core\ViewPHP;

class ViewPHPPreViewTest extends TestCase
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

        $this->templatePath = ROOT . DS . 'tmp' . DS . 'viewphp-pre-test.php';
        file_put_contents($this->templatePath, 'x');
    }

    #[\PHPUnit\Framework\Attributes\After]
    protected function tearDown(): void
    {
        if (file_exists($this->templatePath)) {
            unlink($this->templatePath);
        }
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function preViewSanitizesHtmlContent(): void
    {
        $view = new ViewPHP($this->templatePath);
        $dirty = '<script>alert(1)</script><p>ok</p>';

        $clean = $view->preView($dirty);

        $this->assertStringNotContainsString('<script>', $clean);
        $this->assertStringContainsString('<p>ok</p>', $clean);
    }
}
