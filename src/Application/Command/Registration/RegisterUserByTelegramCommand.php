<?php

declare(strict_types=1);

namespace App\Application\Command\Registration;

final readonly class RegisterUserByTelegramCommand
{
    public function __construct(
        public string $telegramId,
        public string $password,
        public ?string $firstName = null,
        public ?string $lastName = null
    ) {}
}
