<?php

declare(strict_types=1);

namespace App\Infrastructure\Notification\Email;

use Illuminate\Support\Facades\Log;
use App\Domain\Entity\User\ValueObject\Email;
use App\Domain\Entity\User\ValueObject\VerificationCodeValue;

final readonly class EmailSender implements EmailSenderInterface
{
    public function sendVerificationEmail(Email $recipient, VerificationCodeValue $code): void
    {
        Log::info(sprintf('Тестовая отправка письма: Отправление верификации на %s с кодом %s', $recipient->value, $code->value));
    }

    public function sendPasswordResetEmail(Email $recipient, VerificationCodeValue $code): void
    {
        Log::info(sprintf('Тестовая отправка письма: Отправление сброса пароля на %s с кодом %s', $recipient->value, $code->value));
    }
}
