<?php

declare(strict_types=1);

namespace App\Application\Command\Auth;

final readonly class LoginUserByEmailCommand
{
    public function __construct(public string $email, public string $password) {}
}
