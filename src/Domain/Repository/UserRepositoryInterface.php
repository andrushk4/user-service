<?php

declare(strict_types=1);

namespace App\Domain\Repository;

use Ramsey\Uuid\UuidInterface;
use App\Domain\Entity\User\User;
use App\Domain\Entity\User\ValueObject\Email;
use App\Domain\Entity\User\ValueObject\Phone;
use App\Domain\Entity\User\ValueObject\TelegramId;

interface UserRepositoryInterface
{
    public function findById(UuidInterface $id): ?User;
    public function findByEmail(Email $email): ?User;
    public function findByPhone(Phone $phone): ?User;
    public function findByTelegramId(TelegramId $telegramId): ?User;
    public function save(User $user): void;
    public function delete(User $user): void;
}
