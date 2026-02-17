<?php

use PHPUnit\Framework\TestCase;
use pff\Config;

class TestEncryptionModuleForCookies extends \pff\Abs\AModule
{
    public function encrypt($value)
    {
        return 'enc:' . $value;
    }

    public function decrypt($value)
    {
        return str_replace('enc:', '', (string) $value);
    }
}

class CookiesTest extends TestCase
{
    private function setUseEncryption(\pff\modules\Cookies $cookies, bool $enabled): void
    {
        $reflection = new ReflectionProperty(\pff\modules\Cookies::class, '_useEncryption');
        $reflection->setValue($cookies, $enabled);
    }

    private function invokePrivateMethod(\pff\modules\Cookies $cookies, string $method, array $args = [])
    {
        $reflection = new ReflectionMethod(\pff\modules\Cookies::class, $method);

        return $reflection->invokeArgs($cookies, $args);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function encodeAndDecodeFallbackToPlainValueWhenEncryptionModuleIsMissing(): void
    {
        $cookies = (new ReflectionClass(\pff\modules\Cookies::class))->newInstanceWithoutConstructor();
        $this->setUseEncryption($cookies, true);

        $encoded = $this->invokePrivateMethod($cookies, 'encodeCookie', ['hello']);
        $decoded = $this->invokePrivateMethod($cookies, 'decodeCookie', ['hello']);

        $this->assertSame('hello', $encoded);
        $this->assertSame('hello', $decoded);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function encodeAndDecodeUseRegisteredEncryptionModuleWhenAvailable(): void
    {
        $cookies = (new ReflectionClass(\pff\modules\Cookies::class))->newInstanceWithoutConstructor();
        $this->setUseEncryption($cookies, true);

        $encryptionModule = new TestEncryptionModuleForCookies();
        $encryptionModule->setModuleName('encryption');
        $cookies->registerRequiredModule($encryptionModule);

        $encoded = $this->invokePrivateMethod($cookies, 'encodeCookie', ['hello']);
        $decoded = $this->invokePrivateMethod($cookies, 'decodeCookie', ['enc:hello']);

        $this->assertSame('enc:hello', $encoded);
        $this->assertSame('hello', $decoded);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function cookieOptionsUseConfigDefaultsAndHttpsDetection(): void
    {
        $_SERVER['HTTPS'] = 'on';

        $config = $this->createMock(Config::class);
        $config->method('getConfigData')->willReturnCallback(static function ($key) {
            $map = [
                'security_cookie_httponly' => true,
                'security_cookie_samesite' => 'Lax',
                'security_cookie_secure' => null,
            ];

            return array_key_exists($key, $map) ? $map[$key] : false;
        });

        $cookies = (new ReflectionClass(\pff\modules\Cookies::class))->newInstanceWithoutConstructor();
        $cookies->setConfig($config);

        $options = $this->invokePrivateMethod($cookies, 'getCookieOptions', [123]);

        $this->assertSame(123, $options['expires']);
        $this->assertSame('/', $options['path']);
        $this->assertTrue($options['secure']);
        $this->assertTrue($options['httponly']);
        $this->assertSame('Lax', $options['samesite']);
    }
}
