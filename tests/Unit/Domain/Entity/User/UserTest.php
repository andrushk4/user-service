<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Entity\User;

use PHPUnit\Framework\TestCase;
use InvalidArgumentException;
use Ramsey\Uuid\Uuid;
use App\Domain\Entity\User\User;
use App\Domain\Entity\User\ValueObject\Email;
use App\Domain\Entity\User\ValueObject\Phone;
use App\Domain\Entity\User\ValueObject\TelegramId;
use App\Domain\Entity\User\ValueObject\HashedPassword;

class UserTest extends TestCase
{
    public function testRegisterNewWithEmail(): void
    {
        $email = new Email('test@example.com');
        $password = new HashedPassword('hashed_password');
        
        $user = User::registerNew(
            email: $email,
            password: $password,
            firstName: 'Foo',
            lastName: 'Bar'
        );

        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals($email, $user->getEmail());
        $this->assertEquals($password, $user->getPassword());
        $this->assertEquals('Foo', $user->getFirstName());
        $this->assertEquals('Bar', $user->getLastName());
        $this->assertFalse($user->isEmailVerified());
        $this->assertFalse($user->isPhoneVerified());
        $this->assertFalse($user->isTelegramVerified());
        $this->assertFalse($user->isFullyVerified());
    }

    public function testRegisterNewWithPhone(): void
    {
        $phone = new Phone('+1234567890');
        $password = new HashedPassword('hashed_password');
        
        $user = User::registerNew(phone: $phone, password: $password);

        $this->assertEquals($phone, $user->getPhone());
        $this->assertNull($user->getEmail());
        $this->assertNull($user->getTelegramId());
    }

    public function testRegisterNewWithTelegram(): void
    {
        $telegramId = new TelegramId('123456789');
        $password = new HashedPassword('hashed_password');
        
        $user = User::registerNew(telegramId: $telegramId, password: $password);

        $this->assertEquals($telegramId, $user->getTelegramId());
        $this->assertNull($user->getEmail());
        $this->assertNull($user->getPhone());
    }

    public function testRegisterNewWithoutContactThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Нужно указать хотя бы один способ регистрации.');
        
        User::registerNew();
    }

    public function testMarkEmailAsVerified(): void
    {
        $email = new Email('test@example.com');
        $user = User::registerNew(email: $email);

        $this->assertFalse($user->isEmailVerified());
        $this->assertFalse($user->isFullyVerified());

        $user->markEmailAsVerified();

        $this->assertTrue($user->isEmailVerified());
        $this->assertTrue($user->isFullyVerified());
    }

    public function testMarkEmailAsVerifiedWithoutEmailThrowsException(): void
    {
        $phone = new Phone('+1234567890');
        $user = User::registerNew(phone: $phone);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Эмейл не указан.');
        
        $user->markEmailAsVerified();
    }

    public function testMarkPhoneAsVerified(): void
    {
        $phone = new Phone('+1234567890');
        $user = User::registerNew(phone: $phone);

        $this->assertFalse($user->isPhoneVerified());
        
        $user->markPhoneAsVerified();
        
        $this->assertTrue($user->isPhoneVerified());
        $this->assertTrue($user->isFullyVerified());
    }

    public function testMarkTelegramAsVerified(): void
    {
        $telegramId = new TelegramId('123456789');
        $user = User::registerNew(telegramId: $telegramId);

        $this->assertFalse($user->isTelegramVerified());
        
        $user->markTelegramAsVerified();
        
        $this->assertTrue($user->isTelegramVerified());
        $this->assertTrue($user->isFullyVerified());
    }

    public function testChangePassword(): void
    {
        $email = new Email('test@example.com');
        $oldPassword = new HashedPassword('old_password');
        $newPassword = new HashedPassword('new_password');
        
        $user = User::registerNew(email: $email, password: $oldPassword);
        
        $user->changePassword($newPassword);
        
        $this->assertEquals($newPassword, $user->getPassword());
    }

    public function testFromPersistence(): void
    {
        $id = Uuid::uuid4();
        $email = new Email('test@example.com');
        $password = new HashedPassword('hashed_password');
        $createdAt = new \DateTimeImmutable('2023-01-01 10:00:00');
        $updatedAt = new \DateTimeImmutable('2023-01-02 10:00:00');

        $user = User::fromPersistence(
            id: $id,
            email: $email,
            phone: null,
            telegramId: null,
            password: $password,
            isEmailVerified: true,
            isPhoneVerified: false,
            isTelegramVerified: false,
            firstName: 'Foo',
            lastName: 'Bar',
            createdAt: $createdAt,
            updatedAt: $updatedAt
        );

        $this->assertEquals($id, $user->getId());
        $this->assertEquals($email, $user->getEmail());
        $this->assertTrue($user->isEmailVerified());
        $this->assertEquals($createdAt, $user->getCreatedAt());
        $this->assertEquals($updatedAt, $user->getUpdatedAt());
    }
}
