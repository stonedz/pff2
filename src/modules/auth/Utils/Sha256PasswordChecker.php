<?php

namespace pff\modules\Utils;

use pff\modules\Abs\APasswordChecker;

/**
 *
 * @author paolo.fagni<at>gmail.com
 */
class Sha256PasswordChecker extends APasswordChecker
{
    public function checkPass($pass, $encryptedPass, $salt = '')
    {
        if (hash('sha256', $pass.$salt) == $encryptedPass) {
            return true;
        } else {
            return false;
        }
    }
}
