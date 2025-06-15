<?php

declare(strict_types=1);

namespace App\Infrastructure\Notification\Telegram;

use Illuminate\Support\Facades\Log;
use App\Domain\Entity\User\ValueObject\TelegramId;
use App\Domain\Entity\User\ValueObject\VerificationCodeValue;

final readonly class TelegramClient implements TelegramClientInterface
{
    public function sendVerificationCode(TelegramId $recipient, VerificationCodeValue $code): void
    {
        Log::info(sprintf('Тестовая отправка в Telegram: Отправление верификации в Telegram %s с кодом %s', $recipient->value, $code->value));
    }
}
