<?php

use PHPUnit\Framework\TestCase;
use pff\Core\LayoutPHP;
use pff\Core\ServiceContainer;

class LayoutPHPTest extends TestCase
{
    private string $layoutTemplatePath;

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

        $this->layoutTemplatePath = ROOT . DS . 'tmp' . DS . 'layout-php-test.php';
        file_put_contents($this->layoutTemplatePath, '<div>layout</div>');
    }

    #[\PHPUnit\Framework\Attributes\After]
    protected function tearDown(): void
    {
        if (file_exists($this->layoutTemplatePath)) {
            unlink($this->layoutTemplatePath);
        }
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function addContentAndContentRenderNestedView(): void
    {
        $layout = new LayoutPHP($this->layoutTemplatePath);

        $childView = new class ($this->layoutTemplatePath) extends \pff\Abs\AView {
            public function set(string $name, mixed $value): void
            {
            }

            public function render(): void
            {
                echo 'CHILD_RENDERED';
            }

            public function renderHtml(): string
            {
                return 'CHILD_RENDERED';
            }
        };

        $layout->addContent($childView);

        ob_start();
        $layout->content(0);
        $output = (string) ob_get_clean();

        $this->assertSame('CHILD_RENDERED', $output);
        $this->assertCount(1, $layout->getContentViews());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function contentWithMissingIndexDoesNotOutputAnything(): void
    {
        $layout = new LayoutPHP($this->layoutTemplatePath);

        ob_start();
        $layout->content(99);
        $output = (string) ob_get_clean();

        $this->assertSame('', $output);
    }
}
