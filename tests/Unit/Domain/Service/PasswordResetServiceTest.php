<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Service;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use App\Domain\Entity\User\User;
use App\Domain\Service\PasswordResetService;
use PHPUnit\Framework\MockObject\MockObject;
use App\Domain\Entity\User\ValueObject\Email;
use App\Domain\Entity\User\ValueObject\Phone;
use App\Domain\Exception\UserNotFoundException;
use App\Domain\Entity\User\ValueObject\Password;
use App\Domain\Entity\Credential\VerificationCode;
use App\Domain\Repository\UserRepositoryInterface;
use App\Domain\Exception\InvalidCredentialException;
use App\Domain\Entity\User\ValueObject\HashedPassword;
use App\Infrastructure\Security\PasswordHasherInterface;
use App\Domain\Entity\User\ValueObject\VerificationCodeValue;
use App\Domain\Repository\VerificationCodeRepositoryInterface;
use App\Infrastructure\Notification\Email\EmailSenderInterface;

class PasswordResetServiceTest extends TestCase
{
    private UserRepositoryInterface|MockObject $userRepository;
    private VerificationCodeRepositoryInterface|MockObject $verificationCodeRepository;
    private PasswordHasherInterface|MockObject $passwordHasher;
    private EmailSenderInterface|MockObject $emailSender;
    private PasswordResetService $service;

    protected function setUp(): void
    {
        $this->userRepository = $this->createMock(UserRepositoryInterface::class);
        $this->verificationCodeRepository = $this->createMock(VerificationCodeRepositoryInterface::class);
        $this->passwordHasher = $this->createMock(PasswordHasherInterface::class);
        $this->emailSender = $this->createMock(EmailSenderInterface::class);

        $this->service = new PasswordResetService(
            $this->userRepository,
            $this->verificationCodeRepository,
            $this->passwordHasher,
            $this->emailSender
        );
    }

    public function testRequestPasswordResetSuccess(): void
    {
        $email = new Email('test@example.com');
        $user = User::registerNew(email: $email);

        $this->userRepository
            ->expects($this->once())
            ->method('findByEmail')
            ->with($email)
            ->willReturn($user);

        $this->verificationCodeRepository
            ->expects($this->once())
            ->method('save')
            ->with($this->isInstanceOf(VerificationCode::class));

        $this->emailSender
            ->expects($this->once())
            ->method('sendPasswordResetEmail')
            ->with($email, $this->isInstanceOf(VerificationCodeValue::class));

        $this->service->requestPasswordReset($email);
    }

        public function testRequestPasswordResetUserNotFound(): void
    {
        $email = new Email('test@example.com');

        $this->userRepository
            ->expects($this->once())
            ->method('findByEmail')
            ->with($email)
            ->willReturn(null);

        $this->expectException(UserNotFoundException::class);
        $this->expectExceptionMessage('Пользователь не найден.');

        $this->service->requestPasswordReset($email);
    }

    public function testRequestPasswordResetUserWithoutEmail(): void
    {
        $email = new Email('test@example.com');
        $user = User::registerNew(phone: new Phone('+1234567890')); // Пользователь без email

        $this->userRepository
            ->expects($this->once())
            ->method('findByEmail')
            ->with($email)
            ->willReturn($user);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('У пользователя нет email.');

        $this->service->requestPasswordReset($email);
    }

    public function testResetPasswordSuccess(): void
    {
        $email = new Email('test@example.com');
        $code = new VerificationCodeValue('123456');
        $newPassword = new Password('newpassword123');
        $newHashedPassword = new HashedPassword('new_hashed_password');
        
        $user = User::registerNew(email: $email);
        $verificationCode = $this->createMock(VerificationCode::class);
        $verificationCode->method('isExpired')->willReturn(false);
        $verificationCode->method('matches')->with($code)->willReturn(true);
        $verificationCode->method('getUserId')->willReturn($user->getId());

        $this->userRepository
            ->expects($this->once())
            ->method('findByEmail')
            ->with($email)
            ->willReturn($user);

        $this->verificationCodeRepository
            ->expects($this->once())
            ->method('findByEmailAndCode')
            ->with($email, $code)
            ->willReturn($verificationCode);

        $this->passwordHasher
            ->expects($this->once())
            ->method('hash')
            ->with($newPassword)
            ->willReturn($newHashedPassword);

        $this->userRepository
            ->expects($this->once())
            ->method('save')
            ->with($user);

        $this->verificationCodeRepository
            ->expects($this->once())
            ->method('delete')
            ->with($verificationCode);

        $resultUser = $this->service->resetPassword($email, $code, $newPassword);

        $this->assertEquals($user, $resultUser);
        $this->assertEquals($newHashedPassword, $user->getPassword());
    }

    public function testResetPasswordInvalidCode(): void
    {
        $email = new Email('test@example.com');
        $code = new VerificationCodeValue('123456');
        $newPassword = new Password('newpassword123');
        
        $user = User::registerNew(email: $email);

        $this->userRepository
            ->expects($this->once())
            ->method('findByEmail')
            ->with($email)
            ->willReturn($user);

        $this->verificationCodeRepository
            ->expects($this->once())
            ->method('findByEmailAndCode')
            ->with($email, $code)
            ->willReturn(null);

        $this->expectException(InvalidCredentialException::class);
        $this->expectExceptionMessage('Неверный код сброса пароля.');

        $this->service->resetPassword($email, $code, $newPassword);
    }

    public function testResetPasswordExpiredCode(): void
    {
        $email = new Email('test@example.com');
        $code = new VerificationCodeValue('123456');
        $newPassword = new Password('newpassword123');
        
        $user = User::registerNew(email: $email);
        $verificationCode = $this->createMock(VerificationCode::class);
        $verificationCode->method('isExpired')->willReturn(true);
        $verificationCode->method('matches')->with($code)->willReturn(true);
        $verificationCode->method('getUserId')->willReturn($user->getId());

        $this->userRepository
            ->expects($this->once())
            ->method('findByEmail')
            ->with($email)
            ->willReturn($user);

        $this->verificationCodeRepository
            ->expects($this->once())
            ->method('findByEmailAndCode')
            ->with($email, $code)
            ->willReturn($verificationCode);

        $this->expectException(InvalidCredentialException::class);
        $this->expectExceptionMessage('Неверный код сброса пароля.');

        $this->service->resetPassword($email, $code, $newPassword);
    }
}

