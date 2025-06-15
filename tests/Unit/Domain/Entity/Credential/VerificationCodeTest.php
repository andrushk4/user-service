<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Entity\Credential;

use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;
use DateTimeImmutable;
use App\Domain\Entity\Credential\VerificationCode;
use App\Domain\Entity\User\ValueObject\Email;
use App\Domain\Entity\User\ValueObject\Phone;
use App\Domain\Entity\User\ValueObject\TelegramId;
use App\Domain\Entity\User\ValueObject\VerificationCodeValue;
use App\Domain\Enum\VerificationCodeTypeEnum;

class VerificationCodeTest extends TestCase
{
    public function testCreateForEmail(): void
    {
        $userId = Uuid::uuid4();
        $email = new Email('test@example.com');
        
        $verificationCode = VerificationCode::createForEmail($userId, $email, 300);

        $this->assertEquals($userId, $verificationCode->getUserId());
        $this->assertEquals($email, $verificationCode->getEmail());
        $this->assertEquals(VerificationCodeTypeEnum::Email, $verificationCode->getType());
        $this->assertNull($verificationCode->getPhone());
        $this->assertNull($verificationCode->getTelegramId());
        $this->assertFalse($verificationCode->isExpired());
        $this->assertMatchesRegularExpression('/^\d{6}$/', $verificationCode->getCode()->value);
    }

    public function testCreateForPhone(): void
    {
        $userId = Uuid::uuid4();
        $phone = new Phone('+1234567890');
        
        $verificationCode = VerificationCode::createForPhone($userId, $phone, 600);

        $this->assertEquals($userId, $verificationCode->getUserId());
        $this->assertEquals($phone, $verificationCode->getPhone());
        $this->assertEquals(VerificationCodeTypeEnum::Phone, $verificationCode->getType());
        $this->assertNull($verificationCode->getEmail());
        $this->assertNull($verificationCode->getTelegramId());
    }

    public function testCreateForTelegram(): void
    {
        $userId = Uuid::uuid4();
        $telegramId = new TelegramId('123456789');
        
        $verificationCode = VerificationCode::createForTelegram($userId, $telegramId);

        $this->assertEquals($userId, $verificationCode->getUserId());
        $this->assertEquals($telegramId, $verificationCode->getTelegramId());
        $this->assertEquals(VerificationCodeTypeEnum::Telegram, $verificationCode->getType());
        $this->assertNull($verificationCode->getEmail());
        $this->assertNull($verificationCode->getPhone());
    }

    public function testIsExpired(): void
    {
        $userId = Uuid::uuid4();
        $email = new Email('test@example.com');
        
        // Создаем код с TTL 1 секунда
        $verificationCode = VerificationCode::createForEmail($userId, $email, 1);
        
        $this->assertFalse($verificationCode->isExpired());
        
        // Ждем 2 секунды
        sleep(2);
        
        $this->assertTrue($verificationCode->isExpired());
    }

    public function testMatches(): void
    {
        $userId = Uuid::uuid4();
        $email = new Email('test@example.com');
        
        $verificationCode = VerificationCode::createForEmail($userId, $email);
        $correctCode = $verificationCode->getCode();
        $wrongCode = new VerificationCodeValue('999999');

        $this->assertTrue($verificationCode->matches($correctCode));
        $this->assertFalse($verificationCode->matches($wrongCode));
    }

    public function testFromPersistence(): void
    {
        $id = Uuid::uuid4();
        $userId = Uuid::uuid4();
        $code = new VerificationCodeValue('123456');
        $email = new Email('test@example.com');
        $expiresAt = new DateTimeImmutable('+5 minutes');
        $createdAt = new DateTimeImmutable('-1 minute');

        $verificationCode = VerificationCode::fromPersistence(
            id: $id,
            userId: $userId,
            code: $code,
            type: VerificationCodeTypeEnum::Email,
            email: $email,
            phone: null,
            telegramId: null,
            expiresAt: $expiresAt,
            createdAt: $createdAt
        );

        $this->assertEquals($id, $verificationCode->getId());
        $this->assertEquals($userId, $verificationCode->getUserId());
        $this->assertEquals($code, $verificationCode->getCode());
        $this->assertEquals($email, $verificationCode->getEmail());
        $this->assertEquals($createdAt, $verificationCode->getCreatedAt());
        $this->assertEquals($expiresAt, $verificationCode->getExpiresAt());
    }

    public function testExpirationTime(): void
    {
        $userId = Uuid::uuid4();
        $email = new Email('test@example.com');
        $ttl = 1800; // 30 минут
        
        $beforeCreation = new DateTimeImmutable();
        $verificationCode = VerificationCode::createForEmail($userId, $email, $ttl);
        $afterCreation = new DateTimeImmutable();

        $expectedExpiration = $beforeCreation->modify("+{$ttl} seconds");
        $actualExpiration = $verificationCode->getExpiresAt();

        // Проверяем, что время истечения находится в разумных пределах
        $this->assertGreaterThanOrEqual($expectedExpiration->getTimestamp(), $actualExpiration->getTimestamp());
        $this->assertLessThanOrEqual($afterCreation->modify("+{$ttl} seconds")->getTimestamp(), $actualExpiration->getTimestamp());
    }
}
