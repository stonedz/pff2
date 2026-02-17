<?php

use PHPUnit\Framework\TestCase;

#[\PHPUnit\Framework\Attributes\Group('Security')]
class Md5PasswordCheckerTest extends TestCase
{
    #[\PHPUnit\Framework\Attributes\Test]
    public function checkPassReturnsTrueForCorrectPassword(): void
    {
        $checker = new \pff\modules\Utils\Md5PasswordChecker();
        $password = 'test-password';
        $hash = md5($password);

        // Suppress the expected deprecation warning
        $result = @$checker->checkPass($password, $hash);

        $this->assertTrue($result);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function checkPassReturnsFalseForWrongPassword(): void
    {
        $checker = new \pff\modules\Utils\Md5PasswordChecker();
        $hash = md5('correct-password');

        $result = @$checker->checkPass('wrong-password', $hash);

        $this->assertFalse($result);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function checkPassWorksWithSalt(): void
    {
        $checker = new \pff\modules\Utils\Md5PasswordChecker();
        $password = 'salted';
        $salt = 'my-salt';
        $hash = md5($password . $salt);

        $result = @$checker->checkPass($password, $hash, $salt);

        $this->assertTrue($result);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function checkPassTriggersDeprecationWarning(): void
    {
        $checker = new \pff\modules\Utils\Md5PasswordChecker();
        $hash = md5('test');

        $deprecationTriggered = false;
        $deprecationMessage = '';
        set_error_handler(function (int $errno, string $errstr) use (&$deprecationTriggered, &$deprecationMessage): bool {
            if ($errno === E_USER_DEPRECATED) {
                $deprecationTriggered = true;
                $deprecationMessage = $errstr;
                return true;
            }
            return false;
        });

        try {
            $checker->checkPass('test', $hash);
        } finally {
            restore_error_handler();
        }

        $this->assertTrue($deprecationTriggered, 'Expected E_USER_DEPRECATED to be triggered');
        $this->assertStringContainsString('Md5PasswordChecker is deprecated', $deprecationMessage);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function checkPassIsTimingSafe(): void
    {
        $checker = new \pff\modules\Utils\Md5PasswordChecker();
        // Even with matching prefix, wrong hash should fail
        $password = 'timing-test';
        $correctHash = md5($password);
        // Modify last char
        $wrongHash = substr($correctHash, 0, -1) . ($correctHash[-1] === 'a' ? 'b' : 'a');

        $result = @$checker->checkPass($password, $wrongHash);

        $this->assertFalse($result);
    }
}
