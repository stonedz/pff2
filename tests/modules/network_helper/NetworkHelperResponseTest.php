<?php

use PHPUnit\Framework\TestCase;

class NetworkHelperResponseTestDouble extends \pff\modules\NetworkHelper
{
    public array $mockResponse = [];

    public function doCurl($curl_opts)
    {
        return $this->mockResponse;
    }
}

class NetworkHelperResponseTest extends TestCase
{
    #[\PHPUnit\Framework\Attributes\Test]
    public function doGetReturnsEmptyArrayWhenCurlReturnsEmpty(): void
    {
        $helper = new NetworkHelperResponseTestDouble();
        $helper->mockResponse = [];

        $this->assertSame([], $helper->doGet('http://example.test'));
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function doPostReturnsEmptyArrayWhenCurlReturnsEmpty(): void
    {
        $helper = new NetworkHelperResponseTestDouble();
        $helper->mockResponse = [];

        $this->assertSame([], $helper->doPost('http://example.test', 'x=1'));
    }
}
