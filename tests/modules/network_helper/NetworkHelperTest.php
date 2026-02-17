<?php

use PHPUnit\Framework\TestCase;

class NetworkHelperTestDouble extends \pff\modules\NetworkHelper
{
    public array $lastCurlOptions = [];
    public array $responseToReturn = [];

    public function doCurl(array $curl_opts): array
    {
        $this->lastCurlOptions = $curl_opts;
        return $this->responseToReturn;
    }
}

class NetworkHelperTest extends TestCase
{
    #[\PHPUnit\Framework\Attributes\Test]
    public function doCurlReturnsEmptyArrayWhenNoOptionsProvided(): void
    {
        $helper = new \pff\modules\NetworkHelper();
        $this->assertSame([], $helper->doCurl([]));
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function doGetBuildsExpectedCurlOptions(): void
    {
        $helper = new NetworkHelperTestDouble();
        $helper->responseToReturn = ['ok'];

        $result = $helper->doGet('http://example.com/api', 8080, ['Accept: application/json']);

        $this->assertSame(['ok'], $result);
        $this->assertSame('http://example.com/api', $helper->lastCurlOptions[CURLOPT_URL]);
        $this->assertSame(8080, $helper->lastCurlOptions[CURLOPT_PORT]);
        $this->assertFalse($helper->lastCurlOptions[CURLOPT_POST]);
        $this->assertSame(['Accept: application/json'], $helper->lastCurlOptions[CURLOPT_HTTPHEADER]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function doPostBuildsExpectedCurlOptions(): void
    {
        $helper = new NetworkHelperTestDouble();
        $helper->responseToReturn = ['ok'];

        $result = $helper->doPost('http://example.com/api', 'a=1&b=2', 8081, ['Content-Type: application/x-www-form-urlencoded']);

        $this->assertSame(['ok'], $result);
        $this->assertSame('http://example.com/api', $helper->lastCurlOptions[CURLOPT_URL]);
        $this->assertSame(8081, $helper->lastCurlOptions[CURLOPT_PORT]);
        $this->assertTrue($helper->lastCurlOptions[CURLOPT_POST]);
        $this->assertSame('a=1&b=2', $helper->lastCurlOptions[CURLOPT_POSTFIELDS]);
        $this->assertSame(['Content-Type: application/x-www-form-urlencoded'], $helper->lastCurlOptions[CURLOPT_HTTPHEADER]);
    }
}
