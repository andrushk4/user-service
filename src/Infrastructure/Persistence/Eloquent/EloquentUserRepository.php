<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent;

use Exception;
use Ramsey\Uuid\UuidInterface;
use App\Models\User as EloquentUser;
use App\Domain\Entity\User\ValueObject\Email;
use App\Domain\Entity\User\ValueObject\Phone;
use App\Domain\Entity\User\User as DomainUser;
use App\Domain\Entity\User\ValueObject\TelegramId;
use App\Domain\Repository\UserRepositoryInterface;
use App\Infrastructure\Persistence\Mapper\UserMapper;

final readonly class EloquentUserRepository implements UserRepositoryInterface
{
    public function findById(UuidInterface $id): ?DomainUser
    {
        $eloquentUser = EloquentUser::find((string) $id);
        return $eloquentUser ? UserMapper::toDomain($eloquentUser) : null;
    }

    public function findByEmail(Email $email): ?DomainUser
    {
        $eloquentUser = EloquentUser::where('email', $email->value)->first();
        return $eloquentUser ? UserMapper::toDomain($eloquentUser) : null;
    }

    public function findByPhone(Phone $phone): ?DomainUser
    {
        $eloquentUser = EloquentUser::where('phone', $phone->value)->first();
        return $eloquentUser ? UserMapper::toDomain($eloquentUser) : null;
    }

    /**
     * @throws Exception
     */
    public function findByTelegramId(TelegramId $telegramId): ?DomainUser
    {
        $eloquentUser = EloquentUser::where('telegram_id', $telegramId->value)->first();
        return $eloquentUser ? UserMapper::toDomain($eloquentUser) : null;
    }

    public function save(DomainUser $user): void
    {
        $eloquentUser = EloquentUser::find((string) $user->getId());

        if ($eloquentUser) {
            // Обновляем существующую модель
            $eloquentUser = UserMapper::toEloquent($user, $eloquentUser);
        } else {
            // Создаем новую модель
            $eloquentUser = UserMapper::toEloquent($user);
        }
        $eloquentUser->save();
    }

    public function delete(DomainUser $user): void
    {
        EloquentUser::destroy((string) $user->getId());
    }
}
