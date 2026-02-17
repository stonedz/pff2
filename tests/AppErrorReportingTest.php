<?php

use PHPUnit\Framework\TestCase;
use pff\App;
use pff\Config;
use pff\Core\HelperManager;
use pff\Core\HookManager;
use pff\Core\ModuleManager;

#[\PHPUnit\Framework\Attributes\Group('App')]
class AppErrorReportingTest extends TestCase
{
    private int $previousErrorReporting;
    private string $previousDisplayErrors;
    private string $previousLogErrors;
    private string $previousErrorLog;

    #[\PHPUnit\Framework\Attributes\Before]
    protected function setUp(): void
    {
        $this->previousErrorReporting = error_reporting();
        $this->previousDisplayErrors = (string) ini_get('display_errors');
        $this->previousLogErrors = (string) ini_get('log_errors');
        $this->previousErrorLog = (string) ini_get('error_log');
    }

    #[\PHPUnit\Framework\Attributes\After]
    protected function tearDown(): void
    {
        error_reporting($this->previousErrorReporting);
        ini_set('display_errors', $this->previousDisplayErrors);
        ini_set('log_errors', $this->previousLogErrors);
        ini_set('error_log', $this->previousErrorLog);
    }

    private function createAppWithConfig(bool $development, bool $showAllErrors): App
    {
        $config = $this->createMock(Config::class);
        $hookManager = $this->createMock(HookManager::class);
        $moduleManager = $this->createMock(ModuleManager::class);
        $helperManager = $this->createMock(HelperManager::class);

        $map = [
            'development_environment' => $development,
            'show_all_errors' => $showAllErrors,
        ];

        $config->method('getConfigData')
            ->willReturnCallback(static fn($key) => $map[$key] ?? false);

        return new App($config, $hookManager, $moduleManager, $helperManager);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function developmentModeMasksDeprecatedAndNoticeErrorsWhenConfigured(): void
    {
        $app = $this->createAppWithConfig(true, true);
        $app->setErrorReporting();

        $this->assertSame(0, error_reporting() & E_DEPRECATED);
        $this->assertSame(0, error_reporting() & E_NOTICE);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function developmentModeCanEnableAllErrors(): void
    {
        $app = $this->createAppWithConfig(true, false);
        $app->setErrorReporting();

        $this->assertSame(E_ALL, error_reporting());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function productionModeDisablesDisplayAndEnablesLogging(): void
    {
        $app = $this->createAppWithConfig(false, true);
        $app->setErrorReporting();

        $this->assertSame('Off', ini_get('display_errors'));
        $this->assertSame('On', ini_get('log_errors'));
        $this->assertStringContainsString('tmp' . DS . 'logs' . DS . 'error.log', (string) ini_get('error_log'));
    }
}
