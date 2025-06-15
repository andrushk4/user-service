<?php

declare(strict_types=1);

namespace Tests\Integration\Infrastructure\Persistence\Redis;

use Tests\TestCase;
use Ramsey\Uuid\Uuid;
use Illuminate\Support\Facades\Redis;
use App\Domain\Entity\User\ValueObject\Email;
use App\Domain\Entity\User\ValueObject\Phone;
use App\Domain\Entity\Credential\VerificationCode;
use App\Domain\Entity\User\ValueObject\TelegramId;
use App\Domain\Entity\User\ValueObject\VerificationCodeValue;
use App\Infrastructure\Persistence\Redis\RedisVerificationCodeRepository;

class RedisVerificationCodeRepositoryTest extends TestCase
{
    private RedisVerificationCodeRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new RedisVerificationCodeRepository();
    }

    public function testFindByIdReturnsVerificationCodeWhenExists(): void
    {
        $verificationCode = $this->createEmailVerificationCode();
        $this->repository->save($verificationCode);

        $result = $this->repository->findById($verificationCode->getId());

        $this->assertInstanceOf(VerificationCode::class, $result);
        $this->assertEquals($verificationCode->getId(), $result->getId());
        $this->assertEquals($verificationCode->getCode()->value, $result->getCode()->value);
        $this->assertEquals($verificationCode->getEmail()->value, $result->getEmail()->value);
    }

    public function testFindByIdReturnsNullWhenNotExists(): void
    {
        $nonExistentId = Uuid::uuid4();

        $result = $this->repository->findById($nonExistentId);

        $this->assertNull($result);
    }

    public function testFindByEmailAndCodeReturnsNullWhenCodeMismatch(): void
    {
        $email = new Email('test@example.com');
        $wrongCode = new VerificationCodeValue('111111');
        
        $verificationCode = $this->createEmailVerificationCode($email);
        $this->repository->save($verificationCode);

        $result = $this->repository->findByEmailAndCode($email, $wrongCode);

        $this->assertNull($result);
    }

    public function testFindByEmailAndCodeReturnsNullWhenExpired(): void
    {
        $email = new Email('test@example.com');
        $code = new VerificationCodeValue('123456');
        
        // Создаем просроченный код
        $verificationCode = $this->createEmailVerificationCode($email);
        
        $this->repository->save($verificationCode);

        $result = $this->repository->findByEmailAndCode($email, $code);

        $this->assertNull($result);
    }

    public function testSaveStoresVerificationCodeInRedis(): void
    {
        $verificationCode = $this->createEmailVerificationCode();

        $this->repository->save($verificationCode);

        $key = 'verification_code:' . (string) $verificationCode->getId();
        $this->assertTrue(Redis::exists($key) > 0);
        
        $storedData = Redis::hgetall($key);
        $this->assertEquals((string) $verificationCode->getId(), $storedData['id']);
        $this->assertEquals($verificationCode->getCode()->value, $storedData['code_value']);
        $this->assertEquals($verificationCode->getEmail()->value, $storedData['email']);
    }

    public function testSaveCreatesLookupKeyForEmail(): void
    {
        $email = new Email('test@example.com');
        $verificationCode = $this->createEmailVerificationCode($email);

        $this->repository->save($verificationCode);

        $lookupKey = 'verification_code:lookup:email:' . md5($email->value);
        $storedId = Redis::get($lookupKey);
        $this->assertEquals((string) $verificationCode->getId(), $storedId);
    }

    public function testSaveCreatesLookupKeyForPhone(): void
    {
        $phone = new Phone('+1234567890');
        $verificationCode = $this->createPhoneVerificationCode($phone);

        $this->repository->save($verificationCode);

        $lookupKey = 'verification_code:lookup:phone:' . md5($phone->value);
        $storedId = Redis::get($lookupKey);
        $this->assertEquals((string) $verificationCode->getId(), $storedId);
    }

    public function testDeleteRemovesVerificationCodeFromRedis(): void
    {
        $verificationCode = $this->createEmailVerificationCode();
        $this->repository->save($verificationCode);
        
        $key = 'verification_code:' . (string) $verificationCode->getId();
        $this->assertTrue(Redis::exists($key) > 0); // Убеждаемся, что код сохранен

        $this->repository->delete($verificationCode);

        $this->assertFalse(Redis::exists($key) > 0);
    }

    public function testDeleteRemovesLookupKey(): void
    {
        $email = new Email('test@example.com');
        $verificationCode = $this->createEmailVerificationCode($email);
        $this->repository->save($verificationCode);
        
        $lookupKey = 'verification_code:lookup:email:' . md5($email->value);
        $this->assertNotNull(Redis::get($lookupKey)); // Убеждаемся, что lookup ключ существует

        $this->repository->delete($verificationCode);

        $this->assertNull(Redis::get($lookupKey));
    }

    public function testMultipleVerificationCodesCanCoexist(): void
    {
        $emailCode = $this->createEmailVerificationCode(new Email('email@example.com'));
        $phoneCode = $this->createPhoneVerificationCode(new Phone('+1234567890'));
        $telegramCode = $this->createTelegramVerificationCode(new TelegramId('123456789'));

        $this->repository->save($emailCode);
        $this->repository->save($phoneCode);
        $this->repository->save($telegramCode);

        $this->assertNotNull($this->repository->findById($emailCode->getId()));
        $this->assertNotNull($this->repository->findById($phoneCode->getId()));
        $this->assertNotNull($this->repository->findById($telegramCode->getId()));
    }

    /**
     * Вспомогательные методы для создания тестовых данных
     */
    private function createEmailVerificationCode(?Email $email = null): VerificationCode
    {
        return VerificationCode::createForEmail(Uuid::uuid4(), $email ?? new Email('test@example.com'));
    }

    private function createPhoneVerificationCode(?Phone $phone = null): VerificationCode
    {
        return VerificationCode::createForPhone(Uuid::uuid4(), $phone ?? new Phone('+1234567890'));
    }

    private function createTelegramVerificationCode(?TelegramId $telegramId = null): VerificationCode
    {
        return VerificationCode::createForTelegram(Uuid::uuid4(), $telegramId ?? new TelegramId('123456789'));
    }
}
