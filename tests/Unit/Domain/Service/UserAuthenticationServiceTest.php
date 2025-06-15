<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Service;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use App\Domain\Service\UserAuthenticationService;
use App\Domain\Entity\User\User;
use App\Domain\Entity\User\ValueObject\Email;
use App\Domain\Entity\User\ValueObject\Phone;
use App\Domain\Entity\User\ValueObject\Password;
use App\Domain\Entity\User\ValueObject\TelegramId;
use App\Domain\Entity\User\ValueObject\HashedPassword;
use App\Domain\Repository\UserRepositoryInterface;
use App\Infrastructure\Security\PasswordHasherInterface;
use App\Domain\Exception\UserNotFoundException;
use App\Domain\Exception\InvalidCredentialException;

class UserAuthenticationServiceTest extends TestCase
{
    private UserRepositoryInterface|MockObject $userRepository;
    private PasswordHasherInterface|MockObject $passwordHasher;
    private UserAuthenticationService $service;

    protected function setUp(): void
    {
        $this->userRepository = $this->createMock(UserRepositoryInterface::class);
        $this->passwordHasher = $this->createMock(PasswordHasherInterface::class);

        $this->service = new UserAuthenticationService(
            $this->userRepository,
            $this->passwordHasher
        );
    }

    public function testAuthenticateWithEmailSuccess(): void
    {
        $email = new Email('test@example.com');
        $password = new Password('password123');
        $hashedPassword = new HashedPassword('hashed_password');
        
        $user = User::registerNew(email: $email, password: $hashedPassword);
        $user->markEmailAsVerified();

        $this->userRepository
            ->expects($this->once())
            ->method('findByEmail')
            ->with($email)
            ->willReturn($user);

        $this->passwordHasher
            ->expects($this->once())
            ->method('check')
            ->with($password, $hashedPassword)
            ->willReturn(true);

        $authenticatedUser = $this->service->authenticateWithEmail($email, $password);

        $this->assertEquals($user, $authenticatedUser);
    }

    public function testAuthenticateWithEmailUserNotFound(): void
    {
        $email = new Email('test@example.com');
        $password = new Password('password123');

        $this->userRepository
            ->expects($this->once())
            ->method('findByEmail')
            ->with($email)
            ->willReturn(null);

        $this->expectException(UserNotFoundException::class);
        $this->expectExceptionMessage('Пользователь не найден.');

        $this->service->authenticateWithEmail($email, $password);
    }

    public function testAuthenticateWithEmailWrongPassword(): void
    {
        $email = new Email('test@example.com');
        $password = new Password('password123');
        $hashedPassword = new HashedPassword('hashed_password');
        
        $user = User::registerNew(email: $email, password: $hashedPassword);
        $user->markEmailAsVerified();

        $this->userRepository
            ->expects($this->once())
            ->method('findByEmail')
            ->with($email)
            ->willReturn($user);

        $this->passwordHasher
            ->expects($this->once())
            ->method('check')
            ->with($password, $hashedPassword)
            ->willReturn(false);

        $this->expectException(InvalidCredentialException::class);
        $this->expectExceptionMessage('Неверные учетные данные.');

        $this->service->authenticateWithEmail($email, $password);
    }

    public function testAuthenticateWithEmailNotVerified(): void
    {
        $email = new Email('test@example.com');
        $password = new Password('password123');
        $hashedPassword = new HashedPassword('hashed_password');
        
        $user = User::registerNew(email: $email, password: $hashedPassword);
        // Не верифицируем email

        $this->userRepository
            ->expects($this->once())
            ->method('findByEmail')
            ->with($email)
            ->willReturn($user);

        $this->passwordHasher
            ->expects($this->once())
            ->method('check')
            ->with($password, $hashedPassword)
            ->willReturn(true);

        $this->expectException(InvalidCredentialException::class);
        $this->expectExceptionMessage('Аккаунт не верифицирован.');

        $this->service->authenticateWithEmail($email, $password);
    }

    public function testAuthenticateWithPhoneSuccess(): void
    {
        $phone = new Phone('+1234567890');
        $password = new Password('password123');
        $hashedPassword = new HashedPassword('hashed_password');
        
        $user = User::registerNew(phone: $phone, password: $hashedPassword);
        $user->markPhoneAsVerified();

        $this->userRepository
            ->expects($this->once())
            ->method('findByPhone')
            ->with($phone)
            ->willReturn($user);

        $this->passwordHasher
            ->expects($this->once())
            ->method('check')
            ->with($password, $hashedPassword)
            ->willReturn(true);

        $authenticatedUser = $this->service->authenticateWithPhone($phone, $password);

        $this->assertEquals($user, $authenticatedUser);
    }

    public function testAuthenticateWithTelegramSuccess(): void
    {
        $telegramId = new TelegramId('123456789');
        $password = new Password('password123');
        $hashedPassword = new HashedPassword('hashed_password');
        
        $user = User::registerNew(telegramId: $telegramId, password: $hashedPassword);
        $user->markTelegramAsVerified();

        $this->userRepository
            ->expects($this->once())
            ->method('findByTelegramId')
            ->with($telegramId)
            ->willReturn($user);

        $this->passwordHasher
            ->expects($this->once())
            ->method('check')
            ->with($password, $hashedPassword)
            ->willReturn(true);

        $authenticatedUser = $this->service->authenticateWithTelegram($telegramId, $password);

        $this->assertEquals($user, $authenticatedUser);
    }
}
