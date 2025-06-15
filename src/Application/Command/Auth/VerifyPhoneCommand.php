<?php

declare(strict_types=1);

namespace App\Application\Command\Auth;

final readonly class VerifyPhoneCommand
{
    public function __construct(public string $phone, public string $code) {}
}
