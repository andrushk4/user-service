<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Entity\User\ValueObject;

use PHPUnit\Framework\TestCase;
use InvalidArgumentException;
use App\Domain\Entity\User\ValueObject\VerificationCodeValue;

class VerificationCodeValueTest extends TestCase
{
    public function testValidCodeCreation(): void
    {
        $code = new VerificationCodeValue('123456');
        
        $this->assertEquals('123456', $code->value);
        $this->assertEquals('123456', (string) $code);
    }

    public function testCodeEquality(): void
    {
        $code1 = new VerificationCodeValue('123456');
        $code2 = new VerificationCodeValue('123456');
        $code3 = new VerificationCodeValue('654321');

        $this->assertTrue($code1->equals($code2));
        $this->assertFalse($code1->equals($code3));
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('invalidCodeProvider')]
    public function testInvalidCodeThrowsException(string $invalidCode): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Код должен состоять из 6 цифр.');
        
        new VerificationCodeValue($invalidCode);
    }

    public static function invalidCodeProvider(): array
    {
        return [
            [''], // пустой
            ['12345'], // короткий
            ['1234567'], // длинный
            ['12345a'], // с буквой
            ['abcdef'], // только буквы
        ];
    }
}
