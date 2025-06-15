<?php

declare(strict_types=1);

namespace App\Domain\Entity\User;

use Ramsey\Uuid\Uuid;
use DateTimeImmutable;
use InvalidArgumentException;
use Ramsey\Uuid\UuidInterface;
use App\Domain\Entity\User\ValueObject\Email;
use App\Domain\Entity\User\ValueObject\Phone;
use App\Domain\Entity\User\ValueObject\TelegramId;
use App\Domain\Entity\User\ValueObject\HashedPassword;

class User
{
    private UuidInterface $id;
    private ?Email $email = null;
    private ?Phone $phone = null;
    private ?TelegramId $telegramId = null;
    private ?HashedPassword $password = null;
    private bool $isEmailVerified = false;
    private bool $isPhoneVerified = false;
    private bool $isTelegramVerified = false;
    private ?string $firstName = null;
    private ?string $lastName = null;
    private DateTimeImmutable $createdAt;
    private DateTimeImmutable $updatedAt;

    // Приватный конструктор, чтобы принудить использование фабричных методов
    private function __construct(UuidInterface $id)
    {
        $this->id = $id;
        $this->createdAt = new DateTimeImmutable();
        $this->updatedAt = new DateTimeImmutable();
    }

    /**
     * Фабричный метод для создания нового пользователя при регистрации.
     * Доменное правило: хотя бы один способ регистрации должен быть предоставлен.
     */
    public static function registerNew(
        ?Email $email = null,
        ?Phone $phone = null,
        ?TelegramId $telegramId = null,
        ?HashedPassword $password = null,
        ?string $firstName = null,
        ?string $lastName = null
    ): self
    {
        if (is_null($email) && is_null($phone) && is_null($telegramId)) {
            throw new InvalidArgumentException('Нужно указать хотя бы один способ регистрации.');
        }

        $user = new self(Uuid::uuid4());
        $user->email = $email;
        $user->phone = $phone;
        $user->telegramId = $telegramId;
        $user->password = $password;
        $user->firstName = $firstName;
        $user->lastName = $lastName;

        return $user;
    }

    // Статический метод для "пересоздания" User из данных, полученных из репозитория
    public static function fromPersistence(
        UuidInterface $id,
        ?Email $email,
        ?Phone $phone,
        ?TelegramId $telegramId,
        ?HashedPassword $password,
        bool $isEmailVerified,
        bool $isPhoneVerified,
        bool $isTelegramVerified,
        ?string $firstName,
        ?string $lastName,
        DateTimeImmutable $createdAt,
        DateTimeImmutable $updatedAt
    ): self {
        $user = new self($id);
        $user->email = $email;
        $user->phone = $phone;
        $user->telegramId = $telegramId;
        $user->password = $password;
        $user->isEmailVerified = $isEmailVerified;
        $user->isPhoneVerified = $isPhoneVerified;
        $user->isTelegramVerified = $isTelegramVerified;
        $user->firstName = $firstName;
        $user->lastName = $lastName;
        $user->createdAt = $createdAt;
        $user->updatedAt = $updatedAt;
        return $user;
    }

    public function getId(): UuidInterface
    {
        return $this->id;
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

    public function getPassword(): ?HashedPassword
    {
        return $this->password;
    }

    public function isEmailVerified(): bool
    {
        return $this->isEmailVerified;
    }

    public function isPhoneVerified(): bool
    {
        return $this->isPhoneVerified;
    }

    public function isTelegramVerified(): bool
    {
        return $this->isTelegramVerified;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): DateTimeImmutable
    {
        return $this->updatedAt;
    }

    /**
     * @throws InvalidArgumentException
     */
    public function markEmailAsVerified(): void
    {
        if ($this->email === null) {
            throw new InvalidArgumentException('Эмейл не указан.');
        }
        if (!$this->isEmailVerified) {
            $this->isEmailVerified = true;
            $this->updatedAt = new DateTimeImmutable();
        }
    }

    /**
     * @throws InvalidArgumentException
     */
    public function markPhoneAsVerified(): void
    {
        if ($this->phone === null) {
            throw new InvalidArgumentException('Телефон не указан.');
        }
        if (!$this->isPhoneVerified) {
            $this->isPhoneVerified = true;
            $this->updatedAt = new DateTimeImmutable();
        }
    }

    /**
     * @throws InvalidArgumentException
     */
    public function markTelegramAsVerified(): void
    {
        if ($this->telegramId === null) {
            throw new InvalidArgumentException('Телеграм не указан.');
        }
        if (!$this->isTelegramVerified) {
            $this->isTelegramVerified = true;
            $this->updatedAt = new DateTimeImmutable();
        }
    }

    /**
     * @throws InvalidArgumentException
     */
    public function changePassword(HashedPassword $newPassword): void
    {
        if ($this->email === null && $this->phone === null && $this->telegramId === null) {
            throw new InvalidArgumentException('Нет контакта для смены пароля.');
        }
        $this->password = $newPassword;
        $this->updatedAt = new DateTimeImmutable();
    }

    /**
     * Проверяет, что пользователь полностью верифицирован хотя бы по одному каналу
     */
    public function isFullyVerified(): bool
    {
        return $this->isEmailVerified || $this->isPhoneVerified || $this->isTelegramVerified;
    }
}
