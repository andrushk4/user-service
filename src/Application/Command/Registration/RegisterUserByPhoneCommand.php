<?php

declare(strict_types=1);

namespace App\Application\Command\Registration;

final readonly class RegisterUserByPhoneCommand
{
    public function __construct(
        public string $phone,
        public string $password,
        public ?string $firstName = null,
        public ?string $lastName = null
    ) {}
}
