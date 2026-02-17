<?php

use PHPUnit\Framework\TestCase;
use pff\modules\Csrf;
use pff\modules\Exception\CsrfException;

#[\PHPUnit\Framework\Attributes\Group('Security')]
class CsrfModuleTest extends TestCase
{
    #[\PHPUnit\Framework\Attributes\Before]
    protected function setUp(): void
    {
        // Ensure session is available for tests
        if (session_status() === PHP_SESSION_NONE) {
            @session_start();
        }
        // Clean up any previous test tokens
        $_SESSION['_csrf_tokens'] = [];
    }

    #[\PHPUnit\Framework\Attributes\After]
    protected function tearDown(): void
    {
        $_SESSION['_csrf_tokens'] = [];
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function generateTokenReturnsHexString(): void
    {
        $csrf = $this->createCsrfModule();
        $token = $csrf->generateToken('test');

        $this->assertNotEmpty($token);
        $this->assertMatchesRegularExpression('/^[0-9a-f]{64}$/', $token);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function generateTokenStoresInSession(): void
    {
        $csrf = $this->createCsrfModule();
        $token = $csrf->generateToken('form1');

        $this->assertArrayHasKey('form1', $_SESSION['_csrf_tokens']);
        $this->assertSame($token, $_SESSION['_csrf_tokens']['form1']['token']);
        $this->assertArrayHasKey('time', $_SESSION['_csrf_tokens']['form1']);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function validateTokenAcceptsValidToken(): void
    {
        $csrf = $this->createCsrfModule();
        $token = $csrf->generateToken('my_action');

        $result = $csrf->validateToken('my_action', $token);

        $this->assertTrue($result);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function validateTokenRejectsMismatchedToken(): void
    {
        $csrf = $this->createCsrfModule();
        $csrf->generateToken('my_action');

        $this->expectException(CsrfException::class);
        $this->expectExceptionMessage('CSRF token mismatch');
        $csrf->validateToken('my_action', 'wrong-token-value');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function validateTokenRejectsUnknownAction(): void
    {
        $csrf = $this->createCsrfModule();

        $this->expectException(CsrfException::class);
        $this->expectExceptionMessage('not found');
        $csrf->validateToken('unknown_action', 'any-token');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function tokenIsSingleUse(): void
    {
        $csrf = $this->createCsrfModule();
        $token = $csrf->generateToken('once');

        // First validation should succeed
        $csrf->validateToken('once', $token);

        // Second validation should fail — token consumed
        $this->expectException(CsrfException::class);
        $csrf->validateToken('once', $token);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function expiredTokenIsRejected(): void
    {
        $csrf = $this->createCsrfModule();
        $token = $csrf->generateToken('expiry_test');

        // Manually set the timestamp to the past
        $_SESSION['_csrf_tokens']['expiry_test']['time'] = time() - 7200;

        $this->expectException(CsrfException::class);
        $this->expectExceptionMessage('expired');
        $csrf->validateToken('expiry_test', $token);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function tokenFieldReturnsHiddenInput(): void
    {
        $csrf = $this->createCsrfModule();
        $html = $csrf->tokenField('form1');

        $this->assertStringContainsString('<input type="hidden"', $html);
        $this->assertStringContainsString('name="_csrf_token"', $html);
        $this->assertStringContainsString('value="', $html);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function getTokenReturnsStoredTokenWithoutGenerating(): void
    {
        $csrf = $this->createCsrfModule();

        // Before generating, should be null
        $this->assertNull($csrf->getToken('test'));

        // After generating, should return the token
        $token = $csrf->generateToken('test');
        $this->assertSame($token, $csrf->getToken('test'));
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function multipleActionsTrackSeparateTokens(): void
    {
        $csrf = $this->createCsrfModule();
        $token1 = $csrf->generateToken('form_a');
        $token2 = $csrf->generateToken('form_b');

        $this->assertNotSame($token1, $token2);

        // Each validates independently
        $this->assertTrue($csrf->validateToken('form_a', $token1));
        $this->assertTrue($csrf->validateToken('form_b', $token2));
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function getFieldNameReturnsDefault(): void
    {
        $csrf = $this->createCsrfModule();
        $this->assertSame('_csrf_token', $csrf->getFieldName());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function getHeaderNameReturnsDefault(): void
    {
        $csrf = $this->createCsrfModule();
        $this->assertSame('X-CSRF-Token', $csrf->getHeaderName());
    }

    /**
     * Creates a Csrf module instance without requiring the full framework bootstrap.
     */
    private function createCsrfModule(): Csrf
    {
        // The constructor tries to readConfig which requires ROOT/ROOT_LIB constants.
        // It gracefully catches ModuleException when config is not found.
        return new Csrf();
    }
}
