<?php

use PHPUnit\Framework\TestCase;

class MainLayoutTest extends TestCase
{
    #[\PHPUnit\Framework\Attributes\Test]
    public function doBeforeDoesNotSetLayoutWhenViewsAlreadyPresent(): void
    {
        $controller = $this->getMockBuilder(\pff\Abs\AController::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['index', 'getViews', 'setLayout'])
            ->getMock();

        $controller->expects($this->once())->method('getViews')->willReturn([new stdClass()]);
        $controller->expects($this->never())->method('setLayout');

        $outputProperty = new ReflectionProperty(\pff\Abs\AController::class, '_output');
        $outputProperty->setValue($controller, new \pff\Core\Outputs\HTMLOut());

        $module = new \pff\modules\Mainlayout();
        $module->setController($controller);

        $module->doBefore();

        $this->assertTrue(true);
    }
}
