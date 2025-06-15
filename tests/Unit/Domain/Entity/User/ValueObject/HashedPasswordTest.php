<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Entity\User\ValueObject;

use PHPUnit\Framework\TestCase;
use InvalidArgumentException;
use App\Domain\Entity\User\ValueObject\HashedPassword;

class HashedPasswordTest extends TestCase
{
    public function testValidHashedPasswordCreation(): void
    {
        $hashedPassword = new HashedPassword('$2y$10$example.hash.string');
        
        $this->assertEquals('$2y$10$example.hash.string', $hashedPassword->value);
        $this->assertEquals('$2y$10$example.hash.string', (string) $hashedPassword);
    }

    public function testHashedPasswordEquality(): void
    {
        $hash1 = new HashedPassword('$2y$10$example.hash.string');
        $hash2 = new HashedPassword('$2y$10$example.hash.string');
        $hash3 = new HashedPassword('$2y$10$different.hash.string');

        $this->assertTrue($hash1->equals($hash2));
        $this->assertFalse($hash1->equals($hash3));
    }

    public function testEmptyHashedPasswordThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Пароль не может быть пустым.');
        
        new HashedPassword('');
    }
}
