<?php

declare(strict_types=1);

namespace App\Infrastructure\Security;

use App\Domain\Entity\User\User;
use App\Infrastructure\Persistence\Mapper\UserMapper;

final readonly class SanctumTokenGenerator implements AuthTokenGeneratorInterface
{
    public function generateToken(User $user): string
    {
        // Получаем Eloquent модель пользователя из доменной сущности
        $eloquentUser = UserMapper::toEloquent($user);

        // Генерируем токен Laravel Sanctum
        return $eloquentUser->createToken('auth_token')->plainTextToken;
    }
}
