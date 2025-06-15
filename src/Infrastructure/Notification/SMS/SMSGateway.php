<?php

declare(strict_types=1);

namespace App\Infrastructure\Notification\SMS;

use Illuminate\Support\Facades\Log;
use App\Domain\Entity\User\ValueObject\Phone;
use App\Domain\Entity\User\ValueObject\VerificationCodeValue;

final readonly class SMSGateway implements SMSGatewayInterface
{
    public function sendVerificationSMS(Phone $recipient, VerificationCodeValue $code): void
    {
        Log::info(sprintf('Тестовая отправка СМС: Отправление СМС с верификацией на %s с кодом %s', $recipient->value, $code->value));
    }
}
