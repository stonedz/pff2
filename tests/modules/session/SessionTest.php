<?php

use PHPUnit\Framework\TestCase;
use pff\Config;

class SessionTest extends TestCase
{
    #[\PHPUnit\Framework\Attributes\Test]
    #[\PHPUnit\Framework\Attributes\RunInSeparateProcess]
    public function doBeforeSystemAppliesSecureDefaultsAndStartsSession(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_write_close();
        }

        $_SERVER['HTTPS'] = 'on';
        unset($_SERVER['HTTP_X_FORWARDED_PROTO'], $_SERVER['HTTP_X_FORWARDED_SSL']);

        $config = $this->createMock(Config::class);
        $config->method('getConfigData')->willReturnCallback(static function ($key) {
            $map = [
                'security_cookie_httponly' => true,
                'security_cookie_samesite' => 'Lax',
                'security_cookie_secure' => null,
                'security_session_strict_mode' => true,
            ];

            return array_key_exists($key, $map) ? $map[$key] : false;
        });

        $sessionModule = new \pff\modules\Session();
        $sessionModule->setConfig($config);
        $sessionModule->doBeforeSystem();

        $this->assertSame(PHP_SESSION_ACTIVE, session_status());
        $this->assertSame('1', ini_get('session.use_only_cookies'));
        $this->assertSame('1', ini_get('session.use_strict_mode'));

        $cookieParams = session_get_cookie_params();
        $this->assertTrue($cookieParams['secure']);
        $this->assertTrue($cookieParams['httponly']);

        session_destroy();
    }

    #[\PHPUnit\Framework\Attributes\Test]
    #[\PHPUnit\Framework\Attributes\RunInSeparateProcess]
    public function doBeforeSystemForcesSecureCookieWhenSameSiteNone(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_write_close();
        }

        unset($_SERVER['HTTPS']);
        unset($_SERVER['HTTP_X_FORWARDED_PROTO'], $_SERVER['HTTP_X_FORWARDED_SSL']);

        $config = $this->createMock(Config::class);
        $config->method('getConfigData')->willReturnCallback(static function ($key) {
            $map = [
                'security_cookie_httponly' => true,
                'security_cookie_samesite' => 'None',
                'security_cookie_secure' => false,
                'security_session_strict_mode' => true,
            ];

            return $map[$key] ?? false;
        });

        $sessionModule = new \pff\modules\Session();
        $sessionModule->setConfig($config);
        $sessionModule->doBeforeSystem();

        $cookieParams = session_get_cookie_params();
        $this->assertTrue($cookieParams['secure']);

        session_destroy();
    }
}
