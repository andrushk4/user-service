<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Mapper;

use App\Models\User as EloquentUser;
use App\Domain\Entity\User\User as DomainUser;
use Exception;
use Ramsey\Uuid\Uuid;
use DateTimeImmutable;

final readonly class UserMapper
{
    /**
     * Преобразует EloquentUser модель в DomainUser сущность.
     * @throws Exception
     */
    public static function toDomain(EloquentUser $eloquentUser): DomainUser
    {
        return DomainUser::fromPersistence(
            Uuid::fromString($eloquentUser->id->toString()),
            $eloquentUser->email,
            $eloquentUser->phone,
            $eloquentUser->telegram_id,
            $eloquentUser->password,
            $eloquentUser->is_email_verified,
            $eloquentUser->is_phone_verified,
            $eloquentUser->is_telegram_verified,
            $eloquentUser->first_name,
            $eloquentUser->last_name,
            new DateTimeImmutable($eloquentUser->created_at->toIso8601String()),
            new DateTimeImmutable($eloquentUser->updated_at->toIso8601String())
        );
    }

    /**
     * Преобразует DomainUser сущность в EloquentUser модель. Если Eloquent модель уже существует, обновляет ее.
     */
    public static function toEloquent(DomainUser $domainUser, ?EloquentUser $eloquentUser = null): EloquentUser
    {
        $eloquentUser = $eloquentUser ?? new EloquentUser();

        $eloquentUser->id = $domainUser->getId();
        $eloquentUser->email = $domainUser->getEmail();
        $eloquentUser->phone = $domainUser->getPhone();
        $eloquentUser->telegram_id = $domainUser->getTelegramId();
        $eloquentUser->password = $domainUser->getPassword();
        $eloquentUser->is_email_verified = $domainUser->isEmailVerified();
        $eloquentUser->is_phone_verified = $domainUser->isPhoneVerified();
        $eloquentUser->is_telegram_verified = $domainUser->isTelegramVerified();
        $eloquentUser->first_name = $domainUser->getFirstName();
        $eloquentUser->last_name = $domainUser->getLastName();

        if ($eloquentUser->exists) {
            $eloquentUser->updated_at = $domainUser->getUpdatedAt();
        } else {
            $eloquentUser->created_at = $domainUser->getCreatedAt();
            $eloquentUser->updated_at = $domainUser->getUpdatedAt();
        }

        return $eloquentUser;
    }
}
