<?php

declare(strict_types=1);

namespace pff\modules\Utils;

use pff\modules\Abs\APasswordChecker;

/**
 * @deprecated Use PasswordHashPasswordChecker instead. MD5 is not considered secure for password storage.
 * @author paolo.fagni<at>gmail.com
 */
class Md5PasswordChecker extends APasswordChecker
{
    public function checkPass(string $pass, string $encryptedPass, string $salt = ''): bool
    {
        trigger_error(
            'Md5PasswordChecker is deprecated and will be removed in pff2 5.0. '
            . 'Migrate to password_hash/password_verify via passwordType: password_hash in module.conf.yaml.',
            E_USER_DEPRECATED
        );

        return hash_equals($encryptedPass, md5($pass . $salt));
    }
}
