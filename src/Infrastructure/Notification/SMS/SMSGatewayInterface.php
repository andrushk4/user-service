<?php

declare(strict_types=1);

namespace App\Infrastructure\Notification\SMS;

use App\Domain\Entity\User\ValueObject\Phone;
use App\Domain\Entity\User\ValueObject\VerificationCodeValue;

interface SMSGatewayInterface
{
    public function sendVerificationSMS(Phone $recipient, VerificationCodeValue $code): void;
}
