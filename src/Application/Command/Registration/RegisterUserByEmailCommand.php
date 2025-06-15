<?php

declare(strict_types=1);

namespace App\Application\Command\Registration;

final readonly class RegisterUserByEmailCommand
{
    public function __construct(
        public string $email,
        public string $password,
        public ?string $firstName = null,
        public ?string $lastName = null
    ) {}
}
