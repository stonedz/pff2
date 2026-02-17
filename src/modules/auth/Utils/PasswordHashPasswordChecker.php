<?php

declare(strict_types=1);

namespace pff\modules\Utils;

use pff\modules\Abs\APasswordChecker;

/**
 * Verifies passwords generated with password_hash().
 */
class PasswordHashPasswordChecker extends APasswordChecker
{
    public function checkPass(string $pass, string $encryptedPass, string $salt = ''): bool
    {
        return password_verify($pass, $encryptedPass);
    }
}
