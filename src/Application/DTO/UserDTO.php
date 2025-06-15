<?php

declare(strict_types=1);

namespace App\Application\DTO;

use DateTimeImmutable;
use App\Domain\Entity\User\User;

final readonly class UserDTO
{
    public string $createdAt;
    public string $updatedAt;

    public function __construct(
        public string $id,
        public ?string $email,
        public ?string $phoneNumber,
        public ?string $telegramId,
        public bool $isEmailVerified,
        public bool $isPhoneVerified,
        public bool $isTelegramVerified,
        public ?string $firstName,
        public ?string $lastName,
        DateTimeImmutable $createdAt,
        DateTimeImmutable $updatedAt
    ) {
        $this->createdAt = $createdAt->format(DATE_ATOM);
        $this->updatedAt = $updatedAt->format(DATE_ATOM);
    }

    public static function fromDomain(User $user): self
    {
        return new self(
            (string) $user->getId(),
            $user->getEmail()?->value,
            $user->getPhone()?->value,
            $user->getTelegramId()?->value,
            $user->isEmailVerified(),
            $user->isPhoneVerified(),
            $user->isTelegramVerified(),
            $user->getFirstName(),
            $user->getLastName(),
            $user->getCreatedAt(),
            $user->getUpdatedAt()
        );
    }
}
