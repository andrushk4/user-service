<?php

declare(strict_types=1);

namespace App\Domain\Entity\Credential;

use Ramsey\Uuid\Uuid;
use DateTimeImmutable;
use Ramsey\Uuid\UuidInterface;
use App\Domain\Entity\User\ValueObject\Email;
use App\Domain\Entity\User\ValueObject\Phone;
use App\Domain\Entity\User\ValueObject\TelegramId;
use App\Domain\Entity\User\ValueObject\VerificationCodeValue;
use App\Domain\Enum\VerificationCodeTypeEnum;
use Random\RandomException;

class VerificationCode
{
    private DateTimeImmutable $createdAt;

    private function __construct(
        private readonly UuidInterface $id,
        private readonly UuidInterface $userId,
        private readonly VerificationCodeValue $code,
        private readonly VerificationCodeTypeEnum $type,
        private readonly ?Email $email,
        private readonly ?Phone $phone,
        private readonly ?TelegramId $telegramId,
        private readonly DateTimeImmutable $expiresAt
    ) {
        $this->createdAt = new DateTimeImmutable();
    }

    /**
     * @throws RandomException
     */
    public static function createForEmail(UuidInterface $userId, Email $email, int $ttlSeconds = 300): self
    {
        $codeValue = new VerificationCodeValue((string) random_int(100000, 999999));
        $expiresAt = (new DateTimeImmutable())->modify("+$ttlSeconds seconds");
        return new self(Uuid::uuid4(), $userId, $codeValue, VerificationCodeTypeEnum::Email, $email, null, null, $expiresAt);
    }

    /**
     * @throws RandomException
     */
    public static function createForPhone(UuidInterface $userId, Phone $phone, int $ttlSeconds = 300): self
    {
        $codeValue = new VerificationCodeValue((string) random_int(100000, 999999));
        $expiresAt = (new DateTimeImmutable())->modify("+$ttlSeconds seconds");
        return new self(Uuid::uuid4(), $userId, $codeValue, VerificationCodeTypeEnum::Phone, null, $phone, null, $expiresAt);
    }

    /**
     * @throws RandomException
     */
    public static function createForTelegram(UuidInterface $userId, TelegramId $telegramId, int $ttlSeconds = 300): self
    {
        $codeValue = new VerificationCodeValue((string) random_int(100000, 999999));
        $expiresAt = (new DateTimeImmutable())->modify("+$ttlSeconds seconds");
        return new self(Uuid::uuid4(), $userId, $codeValue, VerificationCodeTypeEnum::Telegram, null, null, $telegramId, $expiresAt);
    }

    // Метод для "пересоздания" VerificationCode из данных, полученных из Redis
    public static function fromPersistence(
        UuidInterface $id,
        UuidInterface $userId,
        VerificationCodeValue $code,
        VerificationCodeTypeEnum $type,
        ?Email $email,
        ?Phone $phone,
        ?TelegramId $telegramId,
        DateTimeImmutable $expiresAt,
        DateTimeImmutable $createdAt
    ): self
    {
        $instance = new self($id, $userId, $code, $type, $email, $phone, $telegramId, $expiresAt);
        $instance->createdAt = $createdAt;
        return $instance;
    }

    public function getId(): UuidInterface
    {
        return $this->id;
    }

    public function getUserId(): UuidInterface
    {
        return $this->userId;
    }

    public function getCode(): VerificationCodeValue
    {
        return $this->code;
    }

    public function getType(): VerificationCodeTypeEnum
    {
        return $this->type;
    }

    public function getEmail(): ?Email
    {
        return $this->email;
    }

    public function getPhone(): ?Phone
    {
        return $this->phone;
    }

    public function getTelegramId(): ?TelegramId
    {
        return $this->telegramId;
    }

    public function getExpiresAt(): DateTimeImmutable
    {
        return $this->expiresAt;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    /**
     * Проверяет, не истек ли срок действия кода
     */
    public function isExpired(): bool
    {
        return (new DateTimeImmutable()) > $this->expiresAt;
    }

    /**
     * Проверяет, совпадает ли введенный код с кодом из VerificationCode
     */
    public function matches(VerificationCodeValue $inputCode): bool
    {
        return $this->code->equals($inputCode);
    }
}
