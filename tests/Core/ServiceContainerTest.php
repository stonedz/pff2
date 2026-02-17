<?php

use PHPUnit\Framework\TestCase;
use pff\Core\ServiceContainer;

#[\PHPUnit\Framework\Attributes\Group('Core')]
class ServiceContainerTest extends TestCase
{
    #[\PHPUnit\Framework\Attributes\Before]
    protected function setUp(): void
    {
        ServiceContainer::$pimple = null;
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function initPimpleCreatesContainerOnlyOnce(): void
    {
        ServiceContainer::initPimple();
        $first = ServiceContainer::set();

        ServiceContainer::initPimple();
        $second = ServiceContainer::set();

        $this->assertNotNull($first);
        $this->assertSame($first, $second);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function setAndGetProvideServiceAccess(): void
    {
        ServiceContainer::initPimple();
        ServiceContainer::set()['sample'] = static fn() => 'ok';

        $this->assertSame('ok', ServiceContainer::get('sample'));
    }
}
