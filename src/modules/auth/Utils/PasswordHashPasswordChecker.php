<?php

namespace pff\modules\Utils;

use pff\modules\Abs\APasswordChecker;

/**
 * Verifies passwords generated with password_hash().
 */
class PasswordHashPasswordChecker extends APasswordChecker
{
    public function checkPass($pass, $encryptedPass, $salt = '')
    {
        return password_verify($pass, $encryptedPass);
    }
}
