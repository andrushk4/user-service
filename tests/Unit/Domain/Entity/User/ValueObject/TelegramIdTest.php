<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Entity\User\ValueObject;

use PHPUnit\Framework\TestCase;
use InvalidArgumentException;
use App\Domain\Entity\User\ValueObject\TelegramId;

class TelegramIdTest extends TestCase
{
    public function testValidTelegramIdCreation(): void
    {
        $telegramId = new TelegramId('123456789');
        
        $this->assertEquals('123456789', $telegramId->value);
        $this->assertEquals('123456789', (string) $telegramId);
    }

    public function testTelegramIdEquality(): void
    {
        $telegramId1 = new TelegramId('123456789');
        $telegramId2 = new TelegramId('123456789');
        $telegramId3 = new TelegramId('987654321');

        $this->assertTrue($telegramId1->equals($telegramId2));
        $this->assertFalse($telegramId1->equals($telegramId3));
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('invalidTelegramIdProvider')]
    public function testInvalidTelegramIdThrowsException(string $invalidTelegramId): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Некорректный Telegram ID.');
        
        new TelegramId($invalidTelegramId);
    }

    public static function invalidTelegramIdProvider(): array
    {
        return [
            [''],
            ['abc123'],
        ];
    }

    public function testLargeTelegramId(): void
    {
        $telegramId = new TelegramId('1234567890123456789');
        $this->assertEquals('1234567890123456789', $telegramId->value);
    }
}
