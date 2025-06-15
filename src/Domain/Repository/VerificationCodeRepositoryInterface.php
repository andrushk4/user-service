<?php

declare(strict_types=1);

namespace App\Domain\Repository;

use Ramsey\Uuid\UuidInterface;
use App\Domain\Entity\User\ValueObject\Email;
use App\Domain\Entity\User\ValueObject\Phone;
use App\Domain\Enum\VerificationCodeTypeEnum;
use App\Domain\Entity\Credential\VerificationCode;
use App\Domain\Entity\User\ValueObject\TelegramId;
use App\Domain\Entity\User\ValueObject\VerificationCodeValue;

interface VerificationCodeRepositoryInterface
{
    public function findById(UuidInterface $id): ?VerificationCode;
    public function findByEmailAndCode(Email $email, VerificationCodeValue $code): ?VerificationCode;
    public function findByPhoneAndCode(Phone $phone, VerificationCodeValue $code): ?VerificationCode;
    public function findByTelegramIdAndCode(TelegramId $telegramId, VerificationCodeValue $code): ?VerificationCode;
    public function save(VerificationCode $verificationCode): void;
    public function delete(VerificationCode $verificationCode): void;
}
