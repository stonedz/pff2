<?php

namespace pff\modules\Utils;
use pff\modules\Abs\APasswordChecker;

/**
 *
 * @author paolo.fagni<at>gmail.com
 */
class Md5PasswordChecker extends APasswordChecker {

    public function checkPass($pass, $encryptedPass, $salt = '') {
        if (md5($pass.$salt) == $encryptedPass) {
            return true;
        } else {
            return false;
        }
    }

}
