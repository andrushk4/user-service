<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Entity\User\ValueObject;

use PHPUnit\Framework\TestCase;
use InvalidArgumentException;
use App\Domain\Entity\User\ValueObject\Email;

class EmailTest extends TestCase
{
    public function testValidEmailCreation(): void
    {
        $email = new Email('test@example.com');
        
        $this->assertEquals('test@example.com', $email->value);
        $this->assertEquals('test@example.com', (string) $email);
    }

    public function testEmailEquality(): void
    {
        $email1 = new Email('test@example.com');
        $email2 = new Email('test@example.com');
        $email3 = new Email('other@example.com');

        $this->assertTrue($email1->equals($email2));
        $this->assertFalse($email1->equals($email3));
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('invalidEmailProvider')]
    public function testInvalidEmailThrowsException(string $invalidEmail): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Некорректный адрес электронной почты.');
        
        new Email($invalidEmail);
    }

    public static function invalidEmailProvider(): array
    {
        return [
            ['invalid-email'],
            ['@example.com'],
            ['test@'],
            [''],
            ['test.example.com'],
        ];
    }
}
