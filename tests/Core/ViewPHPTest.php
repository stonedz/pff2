<?php

use PHPUnit\Framework\TestCase;
use pff\Core\ServiceContainer;
use pff\Core\ViewPHP;
use pff\Exception\ViewException;

#[\PHPUnit\Framework\Attributes\Group('Core')]
class ViewPHPTest extends TestCase
{
    private string $templatePath;

    #[\PHPUnit\Framework\Attributes\Before]
    protected function setUp(): void
    {
        ServiceContainer::$pimple = null;
        ServiceContainer::initPimple();
        ServiceContainer::set()['app'] = new class {
            public function getExternalPath()
            {
                return '/';
            }
        };

        $this->templatePath = ROOT . DS . 'tmp' . DS . 'viewphp-test-template.php';
        file_put_contents($this->templatePath, 'Hello <?= $this->get("name") ?>');
    }

    #[\PHPUnit\Framework\Attributes\After]
    protected function tearDown(): void
    {
        if (file_exists($this->templatePath)) {
            unlink($this->templatePath);
        }
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function renderHtmlReturnsRenderedTemplateContent(): void
    {
        ob_start();
        $view = new ViewPHP($this->templatePath);
        $view->set('name', 'World');

        $html = $view->renderHtml();
        ob_end_clean();

        $this->assertSame('Hello World', trim($html));
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function constructorFailsForMissingTemplate(): void
    {
        $this->expectException(ViewException::class);
        new ViewPHP(ROOT . DS . 'tmp' . DS . 'missing-template.php');
    }
}
