<?php

declare(strict_types=1);

namespace App\Infrastructure\Notification\Email;

use App\Domain\Entity\User\ValueObject\Email;
use App\Domain\Entity\User\ValueObject\VerificationCodeValue;

interface EmailSenderInterface
{
    public function sendVerificationEmail(Email $recipient, VerificationCodeValue $code): void;
    public function sendPasswordResetEmail(Email $recipient, VerificationCodeValue $code): void;
}
