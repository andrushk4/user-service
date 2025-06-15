<?php

declare(strict_types=1);

namespace App\Infrastructure\Notification\Telegram;

use App\Domain\Entity\User\ValueObject\TelegramId;
use App\Domain\Entity\User\ValueObject\VerificationCodeValue;

interface TelegramClientInterface
{
    public function sendVerificationCode(TelegramId $recipient, VerificationCodeValue $code): void;
}
