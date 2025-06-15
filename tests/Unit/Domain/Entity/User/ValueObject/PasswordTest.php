<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Entity\User\ValueObject;

use PHPUnit\Framework\TestCase;
use InvalidArgumentException;
use App\Domain\Entity\User\ValueObject\Password;

class PasswordTest extends TestCase
{
    public function testValidPasswordCreation(): void
    {
        $password = new Password('password123');
        
        $this->assertEquals('password123', $password->value);
        $this->assertEquals('password123', (string) $password);
    }

    public function testShortPasswordThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Пароль должен содержать не менее 8 символов.');
        
        new Password('short');
    }

    public function testMinimumLengthPassword(): void
    {
        $password = new Password('12345678');
        $this->assertEquals('12345678', $password->value);
    }
}
