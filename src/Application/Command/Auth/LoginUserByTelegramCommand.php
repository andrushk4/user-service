<?php

declare(strict_types=1);

namespace App\Application\Command\Auth;

final readonly class LoginUserByTelegramCommand
{
    public function __construct(public string $telegramId, public string $password) {}
}
