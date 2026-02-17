<?php

use PHPUnit\Framework\TestCase;
use pff\Core\ServiceContainer;
use pff\Core\ViewPHP;

#[\PHPUnit\Framework\Attributes\Group('Core')]
#[\PHPUnit\Framework\Attributes\Group('Security')]
class ViewPHPSecurityTest extends TestCase
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

        $this->templatePath = ROOT . DS . 'tmp' . DS . 'viewphp-security-test.php';
    }

    #[\PHPUnit\Framework\Attributes\After]
    protected function tearDown(): void
    {
        if (file_exists($this->templatePath)) {
            unlink($this->templatePath);
        }
    }

    // ---- get() / has() / getData() -------------------------------------------

    #[\PHPUnit\Framework\Attributes\Test]
    public function getReturnsSetValue(): void
    {
        file_put_contents($this->templatePath, '');
        $view = new ViewPHP($this->templatePath);
        $view->set('name', 'Alice');

        $this->assertSame('Alice', $view->get('name'));
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function getReturnsDefaultWhenKeyMissing(): void
    {
        file_put_contents($this->templatePath, '');
        $view = new ViewPHP($this->templatePath);

        $this->assertNull($view->get('missing'));
        $this->assertSame('fallback', $view->get('missing', 'fallback'));
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function hasReturnsTrueForExistingKey(): void
    {
        file_put_contents($this->templatePath, '');
        $view = new ViewPHP($this->templatePath);
        $view->set('exists', 'yes');

        $this->assertTrue($view->has('exists'));
        $this->assertFalse($view->has('nope'));
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function getDataReturnsAllVariables(): void
    {
        file_put_contents($this->templatePath, '');
        $view = new ViewPHP($this->templatePath);
        $view->set('a', 1);
        $view->set('b', 2);

        $data = $view->getData();
        $this->assertArrayHasKey('a', $data);
        $this->assertArrayHasKey('b', $data);
        // Also contains pff_path_* set by AView constructor
        $this->assertArrayHasKey('pff_path_public', $data);
    }

    // ---- extract() removal verification --------------------------------------

    #[\PHPUnit\Framework\Attributes\Test]
    public function renderDoesNotExtractVariablesIntoScope(): void
    {
        // Template tries to access $name directly — should produce a warning/empty,
        // but $this->get('name') should work.
        $template = '<?php echo isset($name) ? "EXTRACTED" : "SAFE"; ?>';
        file_put_contents($this->templatePath, $template);

        $view = new ViewPHP($this->templatePath);
        $view->set('name', 'test');

        ob_start();
        $view->render();
        $output = ob_get_clean();

        $this->assertSame('SAFE', $output, 'extract() should not inject variables into template scope');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function renderTemplateCanAccessDataViaThisGet(): void
    {
        $template = '<?= $this->get("greeting") ?>';
        file_put_contents($this->templatePath, $template);

        $view = new ViewPHP($this->templatePath);
        $view->set('greeting', 'Hello World');

        ob_start();
        $view->render();
        $output = ob_get_clean();

        $this->assertSame('Hello World', $output);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function renderHtmlReturnsContentViaThisGet(): void
    {
        $template = '<?= $this->get("msg") ?>';
        file_put_contents($this->templatePath, $template);

        ob_start();
        $view = new ViewPHP($this->templatePath);
        $view->set('msg', 'content');

        $html = $view->renderHtml();
        ob_end_clean();

        $this->assertSame('content', trim($html));
    }

    // ---- e() / escape() -----------------------------------------------------

    #[\PHPUnit\Framework\Attributes\Test]
    public function eEscapesHtmlByDefault(): void
    {
        file_put_contents($this->templatePath, '');
        $view = new ViewPHP($this->templatePath);

        $result = $view->e('<script>alert(1)</script>');

        $this->assertStringNotContainsString('<script>', $result);
        $this->assertStringContainsString('&lt;script&gt;', $result);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function eEscapesAttributes(): void
    {
        file_put_contents($this->templatePath, '');
        $view = new ViewPHP($this->templatePath);

        $result = $view->e('" onmouseover="evil()"', 'attr');

        $this->assertStringNotContainsString('"', $result);
        $this->assertStringContainsString('&quot;', $result);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function eEscapesJsContext(): void
    {
        file_put_contents($this->templatePath, '');
        $view = new ViewPHP($this->templatePath);

        $result = $view->e('</script><script>alert(1)</script>', 'js');

        $this->assertStringNotContainsString('</script>', $result);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function eEscapesUrlContext(): void
    {
        file_put_contents($this->templatePath, '');
        $view = new ViewPHP($this->templatePath);

        $result = $view->e('hello world&foo=bar', 'url');

        $this->assertSame('hello%20world%26foo%3Dbar', $result);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function escapeIsAliasForE(): void
    {
        file_put_contents($this->templatePath, '');
        $view = new ViewPHP($this->templatePath);

        $input = '<b>bold</b>';
        $this->assertSame($view->e($input), $view->escape($input));
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function eWorksInsideTemplate(): void
    {
        $template = '<?= $this->e($this->get("user_input")) ?>';
        file_put_contents($this->templatePath, $template);

        $view = new ViewPHP($this->templatePath);
        $view->set('user_input', '<img src=x onerror=alert(1)>');

        ob_start();
        $view->render();
        $output = ob_get_clean();

        $this->assertStringNotContainsString('<img', $output);
        $this->assertStringContainsString('&lt;img', $output);
    }
}
