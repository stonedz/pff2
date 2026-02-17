<?php

use PHPUnit\Framework\TestCase;

#[\PHPUnit\Framework\Attributes\Group('Security')]
class Sha256PasswordCheckerTest extends TestCase
{
    #[\PHPUnit\Framework\Attributes\Test]
    public function checkPassReturnsTrueForCorrectPassword(): void
    {
        $checker = new \pff\modules\Utils\Sha256PasswordChecker();
        $password = 'test-password';
        $hash = hash('sha256', $password);

        $result = @$checker->checkPass($password, $hash);

        $this->assertTrue($result);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function checkPassReturnsFalseForWrongPassword(): void
    {
        $checker = new \pff\modules\Utils\Sha256PasswordChecker();
        $hash = hash('sha256', 'correct-password');

        $result = @$checker->checkPass('wrong-password', $hash);

        $this->assertFalse($result);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function checkPassWorksWithSalt(): void
    {
        $checker = new \pff\modules\Utils\Sha256PasswordChecker();
        $password = 'salted';
        $salt = 'my-salt';
        $hash = hash('sha256', $password . $salt);

        $result = @$checker->checkPass($password, $hash, $salt);

        $this->assertTrue($result);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function checkPassTriggersDeprecationWarning(): void
    {
        $checker = new \pff\modules\Utils\Sha256PasswordChecker();
        $hash = hash('sha256', 'test');

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
        $this->assertStringContainsString('Sha256PasswordChecker is deprecated', $deprecationMessage);
    }
}
