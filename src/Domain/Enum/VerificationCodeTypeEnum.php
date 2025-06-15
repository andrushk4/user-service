<?php

declare(strict_types=1);

namespace App\Domain\Enum;

enum VerificationCodeTypeEnum: string
{
 case Email = 'email';
 case Phone = 'phone';
 case Telegram = 'telegram';
 case PasswordReset = 'password_reset';
}
