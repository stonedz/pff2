<?php

use PHPUnit\Framework\TestCase;

/**
 *
 * @author paolo.fagni<at>gmail.com
 */
class EncryptionTest extends TestCase
{
    /**
     * @var \pff\modules\Encryption
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp(): void
    {
        $this->object = new \pff\modules\Encryption();
    }

    public function testEncryption(): void
    {
        $string = "A string";
        $encryptedString = $this->object->encrypt($string);
        $decryptedString = $this->object->decrypt($encryptedString);
        $this->assertNotEquals($string, $encryptedString);
        $this->assertEquals($string, $decryptedString);
    }

    public function testEncryptionWithUserSpecifiedKey(): void
    {
        $string = "A string";
        $key    = "aKey";
        $encryptedString = $this->object->encrypt($string, $key);
        $decryptedString = $this->object->decrypt($encryptedString, $key);
        $this->assertNotEquals($string, $encryptedString);
        $this->assertEquals($string, $decryptedString);
    }
}
