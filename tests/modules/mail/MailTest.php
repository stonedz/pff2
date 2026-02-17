<?php

use PHPUnit\Framework\TestCase;

class MailTest extends TestCase
{
    private function buildMailModuleWithoutConstructor(): \pff\modules\Mail
    {
        return (new ReflectionClass(\pff\modules\Mail::class))->newInstanceWithoutConstructor();
    }

    private function invokeLoadConfig(\pff\modules\Mail $mail, array $parsedConfig): void
    {
        $method = new ReflectionMethod(\pff\modules\Mail::class, 'loadConfig');
        $method->invoke($mail, $parsedConfig);
    }

    private function getTransportDsn(\pff\modules\Mail $mail): string
    {
        $property = new ReflectionProperty(\pff\modules\Mail::class, 'transportDsn');

        return (string) $property->getValue($mail);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function smtpConfigWithCredentialsBuildsSmtpsDsnWhenEncryptionIsTls(): void
    {
        $mail = $this->buildMailModuleWithoutConstructor();
        $this->invokeLoadConfig($mail, [
            'moduleConf' => [
                'Type' => 'smtp',
                'Host' => 'smtp.example.com',
                'Port' => 587,
                'Username' => 'user@example.com',
                'Password' => 'pass#1',
                'Encryption' => 'tls',
            ],
        ]);

        $dsn = $this->getTransportDsn($mail);
        $this->assertSame('smtps://user%40example.com:pass%231@smtp.example.com:587', $dsn);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function sendmailTypeBuildsSendmailDsn(): void
    {
        $mail = $this->buildMailModuleWithoutConstructor();
        $this->invokeLoadConfig($mail, [
            'moduleConf' => [
                'Type' => 'sendmail',
            ],
        ]);

        $this->assertSame('sendmail://default', $this->getTransportDsn($mail));
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function mailTypeBuildsNativeDsn(): void
    {
        $mail = $this->buildMailModuleWithoutConstructor();
        $this->invokeLoadConfig($mail, [
            'moduleConf' => [
                'Type' => 'mail',
            ],
        ]);

        $this->assertSame('native://default', $this->getTransportDsn($mail));
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function lowercaseConfigKeysAreSupported(): void
    {
        $mail = $this->buildMailModuleWithoutConstructor();
        $this->invokeLoadConfig($mail, [
            'moduleConf' => [
                'type' => 'smtp',
                'host' => 'localhost',
                'port' => 1025,
                'encryption' => '',
            ],
        ]);

        $this->assertSame('smtp://localhost:1025', $this->getTransportDsn($mail));
    }
}
