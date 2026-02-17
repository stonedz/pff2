<?php

use PHPUnit\Framework\TestCase;

class UrlTest extends TestCase
{
    private \pff\modules\Url $url;

    #[\PHPUnit\Framework\Attributes\Before]
    protected function setUp(): void
    {
        $this->url = new \pff\modules\Url();
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function clearStringNormalizesSpecialCharactersAndSeparators(): void
    {
        if (!function_exists('iconv')) {
            $this->markTestSkipped('iconv extension is required for Url::clear_string.');
        }

        $value = $this->url->clear_string('Hello / Wörld + Test!');
        $this->assertSame('hello-world-test', $value);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function makeUrlPrefixesIdAndSlug(): void
    {
        if (!function_exists('iconv')) {
            $this->markTestSkipped('iconv extension is required for Url::make_url.');
        }

        $value = $this->url->make_url(42, 'My Product Name');
        $this->assertSame('42-my-product-name', $value);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function getIdExtractsPrefixBeforeDash(): void
    {
        $value = $this->url->get_id('999-some-title');
        $this->assertSame('999', $value);
    }
}
