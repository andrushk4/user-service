<?php

declare(strict_types=1);

namespace App\Application\DTO;

final readonly class AuthResultDTO
{
    public function __construct(
        public UserDTO $user,
        public string $token,
        public string $tokenType = 'Bearer',
        public ?int $expiresIn = null)
    {}
}
