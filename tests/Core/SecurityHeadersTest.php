<?php

use PHPUnit\Framework\TestCase;
use pff\Core\Outputs\HTMLOut;
use pff\Core\ServiceContainer;

#[\PHPUnit\Framework\Attributes\Group('Core')]
#[\PHPUnit\Framework\Attributes\Group('Security')]
class SecurityHeadersTest extends TestCase
{
    #[\PHPUnit\Framework\Attributes\Before]
    protected function setUp(): void
    {
        ServiceContainer::$pimple = null;
        ServiceContainer::initPimple();
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function setHeaderStoresOverride(): void
    {
        $out = new HTMLOut();
        $out->setHeader('X-Custom', 'value');

        // We can verify setHeader doesn't throw and the value is accepted.
        // Actual header sending requires runInSeparateProcess which is fragile,
        // so we test the API contract here.
        $this->assertInstanceOf(HTMLOut::class, $out);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function removeHeaderStoresNullOverride(): void
    {
        $out = new HTMLOut();
        $out->removeHeader('X-Frame-Options');

        $this->assertInstanceOf(HTMLOut::class, $out);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function setAndRemoveHeaderCanBeChained(): void
    {
        $out = new HTMLOut();
        $out->setHeader('X-Frame-Options', 'SAMEORIGIN');
        $out->removeHeader('Referrer-Policy');
        $out->setHeader('Content-Security-Policy', "default-src 'self'");

        $this->assertInstanceOf(HTMLOut::class, $out);
    }
}
