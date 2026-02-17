<?php

declare(strict_types=1);

namespace pff\modules\Abs;

/**
 *
 * @author paolo.fagni<at>gmail.com
 */
abstract class APasswordChecker
{
    /**
     * @abstract
     * @param string $pass Provided password (NOT encrypted)
     * @param string $encryptedPass Encrypted password
     * @param string $salt If present, the salt used to encrypt
     * @return bool
     */
    abstract public function checkPass(string $pass, string $encryptedPass, string $salt = ''): bool;
}
