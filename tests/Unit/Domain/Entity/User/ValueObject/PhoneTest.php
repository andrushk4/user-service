<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Entity\User\ValueObject;

use PHPUnit\Framework\TestCase;
use InvalidArgumentException;
use App\Domain\Entity\User\ValueObject\Phone;

class PhoneTest extends TestCase
{
    public function testValidPhoneCreation(): void
    {
        $phone = new Phone('+1234567890');
        
        $this->assertEquals('+1234567890', $phone->value);
        $this->assertEquals('+1234567890', (string) $phone);
    }

    public function testPhoneEquality(): void
    {
        $phone1 = new Phone('+1234567890');
        $phone2 = new Phone('+1234567890');
        $phone3 = new Phone('+9876543210');

        $this->assertTrue($phone1->equals($phone2));
        $this->assertFalse($phone1->equals($phone3));
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('invalidPhoneProvider')]
    public function testInvalidPhoneThrowsException(string $invalidPhone): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Некорректный номер телефона. Должен быть в формате +123456789012345.');
        
        new Phone($invalidPhone);
    }

    public static function invalidPhoneProvider(): array
    {
        return [
            ['1234567890'], // без +
            ['+0123456789'], // начинается с 0
            [''], // пустой
            ['+'], // только +
            ['+12345678901234567890'], // слишком длинный
        ];
    }
}
