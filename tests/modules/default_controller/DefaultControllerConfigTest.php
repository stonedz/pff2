<?php

use PHPUnit\Framework\TestCase;

class DefaultControllerConfigTest extends TestCase
{
    #[\PHPUnit\Framework\Attributes\Test]
    public function loadConfigSetsConfiguredDefaultController(): void
    {
        $module = (new ReflectionClass(\pff\modules\DefaultController::class))->newInstanceWithoutConstructor();
        $module->loadConfig([
            'moduleConf' => [
                'defaultController' => 'fallback_controller',
            ],
        ]);

        $property = new ReflectionProperty(\pff\modules\DefaultController::class, '_defaultController');

        $this->assertSame('fallback_controller', $property->getValue($module));
    }
}
