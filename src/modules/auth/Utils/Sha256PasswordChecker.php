<?php

namespace pff\modules\Utils;

use pff\modules\Abs\APasswordChecker;

/**
 * @deprecated Use PasswordHashPasswordChecker instead. SHA256 is not considered secure for password storage.
 * @author paolo.fagni<at>gmail.com
 */
class Sha256PasswordChecker extends APasswordChecker
{
    public function checkPass($pass, $encryptedPass, $salt = '')
    {
        trigger_error(
            'Sha256PasswordChecker is deprecated and will be removed in pff2 5.0. '
            . 'Migrate to password_hash/password_verify via passwordType: password_hash in module.conf.yaml.',
            E_USER_DEPRECATED
        );

        return hash_equals($encryptedPass, hash('sha256', $pass . $salt));
    }
}
