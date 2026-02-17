<?php

declare(strict_types=1);

namespace pff\modules;

use pff\Abs\AModule;

/**
 * Helper module to encrypt/decrypt content
 *
 * @author paolo.fagni<at>gmail.com
 */
class Encryption extends AModule
{
    public const METHOD = 'aes-256-ctr';

    /**
     * The cypher method to be used
     *
     * @var string
     */
    private readonly string $_cypher;

    /**
     * md5 of the key used to encrypt/decrypt data
     *
     * @var string
     */
    private string $_key;

    public function __construct(string $confFile = 'encryption/module.conf.yaml')
    {
        $this->loadConfig($this->readConfig($confFile));
    }

    /**
     * Parse the configuration file
     *
     * @param array $parsedConfig
     */
    private function loadConfig(array $parsedConfig): void
    {
        $key = (string) ($parsedConfig['moduleConf']['key'] ?? '');

        if ($key !== '' && ctype_xdigit($key) && strlen($key) % 2 === 0) {
            $decoded = hex2bin($key);
            $this->_key = $decoded === false ? $key : $decoded;
            return;
        }

        $this->_key = $key;
    }


    /**
     * Encrypts (but does not authenticate) a message
     *
     * @param string $message - plaintext message
     * @param string $key - encryption key (raw binary expected)
     * @param boolean $encode - set to TRUE to return a base64-encoded
     * @return string (raw binary)
     */
    public static function encrypt(string $message, string $key = '', bool $encode = true): string
    {
        $nonceSize = openssl_cipher_iv_length(self::METHOD);
        $nonce = openssl_random_pseudo_bytes($nonceSize);

        $ciphertext = openssl_encrypt(
            $message,
            self::METHOD,
            $key,
            OPENSSL_RAW_DATA,
            $nonce
        );

        // Now let's pack the IV and the ciphertext together
        // Naively, we can just concatenate
        if ($encode) {
            return base64_encode($nonce . $ciphertext);
        }
        return $nonce . $ciphertext;
    }

    /**
     * Decrypts (but does not verify) a message
     *
     * @param string $message - ciphertext message
     * @param string $key - encryption key (raw binary expected)
     * @param boolean $encoded - are we expecting an encoded string?
     * @return string
     */
    public static function decrypt(string $message, string $key = '', bool $encoded = true): string|false
    {
        if ($encoded) {
            $message = base64_decode($message, true);
            if ($message === false) {
                throw new \Exception('Encryption failure');
            }
        }

        $nonceSize = openssl_cipher_iv_length(self::METHOD);
        $nonce = mb_substr($message, 0, $nonceSize, '8bit');
        $ciphertext = mb_substr($message, $nonceSize, null, '8bit');

        $plaintext = openssl_decrypt(
            $ciphertext,
            self::METHOD,
            $key,
            OPENSSL_RAW_DATA,
            $nonce
        );

        return $plaintext;
    }
}
