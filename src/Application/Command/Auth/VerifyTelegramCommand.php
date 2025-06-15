<?php

declare(strict_types=1);

namespace App\Application\Command\Auth;

final readonly class VerifyTelegramCommand
{
    public function __construct(public string $telegramId, public string $code) {}
}
