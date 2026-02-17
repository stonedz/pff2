<?php

use PHPUnit\Framework\TestCase;

class PasswordHashPasswordCheckerTest extends TestCase
{
    #[\PHPUnit\Framework\Attributes\Test]
    public function checkPassValidatesPasswordHash(): void
    {
        $checker = new \pff\modules\Utils\PasswordHashPasswordChecker();
        $password = 'super-secret-password';
        $hash = password_hash($password, PASSWORD_DEFAULT);

        $this->assertTrue($checker->checkPass($password, $hash));
        $this->assertFalse($checker->checkPass('wrong-password', $hash));
    }
}
