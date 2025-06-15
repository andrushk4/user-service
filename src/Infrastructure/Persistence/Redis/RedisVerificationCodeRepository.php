<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Redis;

use Ramsey\Uuid\Uuid;
use DateTimeImmutable;
use Ramsey\Uuid\UuidInterface;
use Illuminate\Support\Facades\Redis;
use App\Domain\Entity\User\ValueObject\Email;
use App\Domain\Entity\User\ValueObject\Phone;
use App\Domain\Enum\VerificationCodeTypeEnum;
use App\Domain\Entity\Credential\VerificationCode;
use App\Domain\Entity\User\ValueObject\TelegramId;
use App\Domain\Entity\User\ValueObject\VerificationCodeValue;
use App\Domain\Repository\VerificationCodeRepositoryInterface;

final readonly class RedisVerificationCodeRepository implements VerificationCodeRepositoryInterface
{
    private const string KEY_PREFIX = 'verification_code:';

    private function generateKey(UuidInterface $id): string
    {
        return self::KEY_PREFIX . (string) $id;
    }

    private function generateLookupKey(VerificationCodeTypeEnum $type, string $credentialValue): string
    {
        return self::KEY_PREFIX . "lookup:{$type->value}:" . md5($credentialValue); // Используем MD5 для хэширования значения, чтобы избежать слишком длинных ключей
    }

    /**
     * Сериализует VerificationCode в массив для хранения в Redis.
     */
    private function serialize(VerificationCode $code): array
    {
        return [
            'id' => (string) $code->getId(),
            'user_id' => (string) $code->getUserId(),
            'code_value' => $code->getCode()->value,
            'type' => $code->getType()->value,
            'email' => $code->getEmail()?->value,
            'phone' => $code->getPhone()?->value,
            'telegram_id' => $code->getTelegramId()?->value,
            'expires_at' => $code->getExpiresAt()->getTimestamp(), // Храним как Unix timestamp
            'created_at' => $code->getCreatedAt()->getTimestamp(),
        ];
    }

    /**
     * Десериализует массив из Redis обратно в VerificationCode.
     */
    private function deserialize(array $data): VerificationCode
    {
        $id = Uuid::fromString($data['id']);
        $userId = Uuid::fromString($data['user_id']);
        $codeValue = new VerificationCodeValue($data['code_value']);
        $type = VerificationCodeTypeEnum::from($data['type']);
        $email = !empty($data['email']) ? new Email($data['email']) : null;
        $phone = !empty($data['phone']) ? new Phone($data['phone']) : null;
        $telegramId = !empty($data['telegram_id']) ? new TelegramId($data['telegram_id']) : null;
        $expiresAt = (new DateTimeImmutable())->setTimestamp((int) $data['expires_at']);
        $createdAt = (new DateTimeImmutable())->setTimestamp((int) $data['created_at']);

        return VerificationCode::fromPersistence(
            $id,
            $userId,
            $codeValue,
            $type,
            $email,
            $phone,
            $telegramId,
            $expiresAt,
            $createdAt
        );
    }

    public function findById(UuidInterface $id): ?VerificationCode
    {
        $data = Redis::hgetall($this->generateKey($id));
        return !empty($data) ? $this->deserialize($data) : null;
    }

    public function findByEmailAndCode(Email $email, VerificationCodeValue $code): ?VerificationCode
    {
        $id = Redis::get($this->generateLookupKey(VerificationCodeTypeEnum::Email, $email->value));
        if ($id) {
            $verificationCode = $this->findById(Uuid::fromString($id));
            if ($verificationCode && $verificationCode->matches($code) && !$verificationCode->isExpired()) {
                return $verificationCode;
            }
        }
        return null;
    }

    public function findByPhoneAndCode(Phone $phone, VerificationCodeValue $code): ?VerificationCode
    {
        $id = Redis::get($this->generateLookupKey(VerificationCodeTypeEnum::Phone, $phone->value));
        if ($id) {
            $verificationCode = $this->findById(Uuid::fromString($id));
            if ($verificationCode && $verificationCode->matches($code) && !$verificationCode->isExpired()) {
                return $verificationCode;
            }
        }
        return null;
    }

    public function findByTelegramIdAndCode(TelegramId $telegramId, VerificationCodeValue $code): ?VerificationCode
    {
        $id = Redis::get($this->generateLookupKey(VerificationCodeTypeEnum::Telegram, $telegramId->value));
        if ($id) {
            $verificationCode = $this->findById(Uuid::fromString($id));
            if ($verificationCode && $verificationCode->matches($code) && !$verificationCode->isExpired()) {
                return $verificationCode;
            }
        }
        return null;
    }

    public function save(VerificationCode $verificationCode): void
    {
        $key = $this->generateKey($verificationCode->getId());
        $serializedData = $this->serialize($verificationCode);
        $ttl = $verificationCode->getExpiresAt()->getTimestamp() - (new DateTimeImmutable())->getTimestamp();

        // Сохраняем данные кода
        Redis::hmset($key, $serializedData);
        Redis::expire($key, max(1, $ttl));

        // Сохраняем lookup-ключ
        $credentialValue = match ($verificationCode->getType()) {
            VerificationCodeTypeEnum::Email, VerificationCodeTypeEnum::PasswordReset => $verificationCode->getEmail()?->value,
            VerificationCodeTypeEnum::Phone => $verificationCode->getPhone()?->value,
            VerificationCodeTypeEnum::Telegram => $verificationCode->getTelegramId()?->value,
        };

        if ($credentialValue) {
            $lookupKey = $this->generateLookupKey($verificationCode->getType(), $credentialValue);
            Redis::setex($lookupKey, max(1, $ttl), (string) $verificationCode->getId());
        }
    }

    public function delete(VerificationCode $verificationCode): void
    {
        $key = $this->generateKey($verificationCode->getId());
        Redis::del($key);

        $credentialValue = match ($verificationCode->getType()) {
            VerificationCodeTypeEnum::Email, VerificationCodeTypeEnum::PasswordReset => $verificationCode->getEmail()?->value,
            VerificationCodeTypeEnum::Phone => $verificationCode->getPhone()?->value,
            VerificationCodeTypeEnum::Telegram => $verificationCode->getTelegramId()?->value,
        };

        if ($credentialValue) {
            $lookupKey = $this->generateLookupKey($verificationCode->getType(), $credentialValue);
            Redis::del($lookupKey);
        }
    }
}
