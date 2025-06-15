<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Service;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use App\Domain\Entity\User\User;
use PHPUnit\Framework\MockObject\MockObject;
use App\Domain\Entity\User\ValueObject\Email;
use App\Domain\Entity\User\ValueObject\Phone;
use App\Domain\Exception\UserNotFoundException;
use App\Domain\Service\UserRegistrationService;
use App\Domain\Entity\User\ValueObject\Password;
use App\Domain\Entity\Credential\VerificationCode;
use App\Domain\Entity\User\ValueObject\TelegramId;
use App\Domain\Repository\UserRepositoryInterface;
use App\Domain\Exception\InvalidCredentialException;
use App\Domain\Entity\User\ValueObject\HashedPassword;
use App\Infrastructure\Security\PasswordHasherInterface;
use App\Infrastructure\Notification\SMS\SMSGatewayInterface;
use App\Domain\Entity\User\ValueObject\VerificationCodeValue;
use App\Domain\Repository\VerificationCodeRepositoryInterface;
use App\Infrastructure\Notification\Email\EmailSenderInterface;
use App\Infrastructure\Notification\Telegram\TelegramClientInterface;

class UserRegistrationServiceTest extends TestCase
{
    private UserRepositoryInterface|MockObject $userRepository;
    private VerificationCodeRepositoryInterface|MockObject $verificationCodeRepository;
    private PasswordHasherInterface|MockObject $passwordHasher;
    private SMSGatewayInterface|MockObject $smsGateway;
    private EmailSenderInterface|MockObject $emailSender;
    private TelegramClientInterface|MockObject $telegramClient;
    private UserRegistrationService $service;

    protected function setUp(): void
    {
        $this->userRepository = $this->createMock(UserRepositoryInterface::class);
        $this->verificationCodeRepository = $this->createMock(VerificationCodeRepositoryInterface::class);
        $this->passwordHasher = $this->createMock(PasswordHasherInterface::class);
        $this->smsGateway = $this->createMock(SMSGatewayInterface::class);
        $this->emailSender = $this->createMock(EmailSenderInterface::class);
        $this->telegramClient = $this->createMock(TelegramClientInterface::class);

        $this->service = new UserRegistrationService(
            $this->userRepository,
            $this->verificationCodeRepository,
            $this->passwordHasher,
            $this->smsGateway,
            $this->emailSender,
            $this->telegramClient
        );
    }

    public function testRegisterWithEmailSuccess(): void
    {
        $email = new Email('test@example.com');
        $password = new Password('password123');
        $hashedPassword = new HashedPassword('hashed_password');

        $this->userRepository
            ->expects($this->once())
            ->method('findByEmail')
            ->with($email)
            ->willReturn(null);

        $this->passwordHasher
            ->expects($this->once())
            ->method('hash')
            ->with($password)
            ->willReturn($hashedPassword);

        $this->userRepository
            ->expects($this->once())
            ->method('save')
            ->with($this->isInstanceOf(User::class));

        $this->verificationCodeRepository
            ->expects($this->once())
            ->method('save');

        $this->emailSender
            ->expects($this->once())
            ->method('sendVerificationEmail')
            ->with($email, $this->isInstanceOf(VerificationCodeValue::class));

        $user = $this->service->registerWithEmail($email, $password, 'Foo', 'Bar');

        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals($email, $user->getEmail());
        $this->assertEquals('Foo', $user->getFirstName());
        $this->assertEquals('Bar', $user->getLastName());
    }

    public function testRegisterWithEmailWhenUserExists(): void
    {
        $email = new Email('test@example.com');
        $password = new Password('password123');
        $existingUser = User::registerNew(email: $email);

        $this->userRepository
            ->expects($this->once())
            ->method('findByEmail')
            ->with($email)
            ->willReturn($existingUser);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Пользователь с таким email уже существует.');

        $this->service->registerWithEmail($email, $password);
    }

    public function testVerifyEmailSuccess(): void
    {
        $email = new Email('test@example.com');
        $code = new VerificationCodeValue('123456');
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

        $this->userRepository
            ->expects($this->once())
            ->method('save')
            ->with($user);

        $this->verificationCodeRepository
            ->expects($this->once())
            ->method('delete')
            ->with($verificationCode);

        $resultUser = $this->service->verifyEmail($email, $code);

        $this->assertTrue($resultUser->isEmailVerified());
    }

        public function testVerifyEmailWithInvalidCode(): void
    {
        $email = new Email('test@example.com');
        $code = new VerificationCodeValue('123456');
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
        $this->expectExceptionMessage('Неверный код или истек срок его действия.');

        $this->service->verifyEmail($email, $code);
    }

    public function testVerifyEmailUserNotFound(): void
    {
        $email = new Email('test@example.com');
        $code = new VerificationCodeValue('123456');

        $this->userRepository
            ->expects($this->once())
            ->method('findByEmail')
            ->with($email)
            ->willReturn(null);

        $this->expectException(UserNotFoundException::class);
        $this->expectExceptionMessage('Пользователь не найден.');

        $this->service->verifyEmail($email, $code);
    }

    public function testRegisterWithPhoneSuccess(): void
    {
        $phone = new Phone('+1234567890');
        $password = new Password('password123');
        $hashedPassword = new HashedPassword('hashed_password');

        $this->userRepository
            ->expects($this->once())
            ->method('findByPhone')
            ->with($phone)
            ->willReturn(null);

        $this->passwordHasher
            ->expects($this->once())
            ->method('hash')
            ->with($password)
            ->willReturn($hashedPassword);

        $this->userRepository
            ->expects($this->once())
            ->method('save')
            ->with($this->isInstanceOf(User::class));

        $this->verificationCodeRepository
            ->expects($this->once())
            ->method('save');

        $this->smsGateway
            ->expects($this->once())
            ->method('sendVerificationSMS')
            ->with($phone, $this->isInstanceOf(VerificationCodeValue::class));

        $user = $this->service->registerWithPhone($phone, $password);

        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals($phone, $user->getPhone());
    }

    public function testRegisterWithTelegramSuccess(): void
    {
        $telegramId = new TelegramId('123456789');
        $password = new Password('password123');
        $hashedPassword = new HashedPassword('hashed_password');

        $this->userRepository
            ->expects($this->once())
            ->method('findByTelegramId')
            ->with($telegramId)
            ->willReturn(null);

        $this->passwordHasher
            ->expects($this->once())
            ->method('hash')
            ->with($password)
            ->willReturn($hashedPassword);

        $this->userRepository
            ->expects($this->once())
            ->method('save')
            ->with($this->isInstanceOf(User::class));

        $this->verificationCodeRepository
            ->expects($this->once())
            ->method('save');

        $this->telegramClient
            ->expects($this->once())
            ->method('sendVerificationCode')
            ->with($telegramId, $this->isInstanceOf(VerificationCodeValue::class));

        $user = $this->service->registerWithTelegram($telegramId, $password);

        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals($telegramId, $user->getTelegramId());
    }

    public function testVerifyPhoneSuccess(): void
    {
        $phone = new Phone('+1234567890');
        $code = new VerificationCodeValue('123456');
        $user = User::registerNew(phone: $phone);
        $verificationCode = $this->createMock(VerificationCode::class);
        $verificationCode->method('isExpired')->willReturn(false);
        $verificationCode->method('matches')->with($code)->willReturn(true);
        $verificationCode->method('getUserId')->willReturn($user->getId());

        $this->userRepository
            ->expects($this->once())
            ->method('findByPhone')
            ->with($phone)
            ->willReturn($user);

        $this->verificationCodeRepository
            ->expects($this->once())
            ->method('findByPhoneAndCode')
            ->with($phone, $code)
            ->willReturn($verificationCode);

        $this->userRepository
            ->expects($this->once())
            ->method('save')
            ->with($user);

        $this->verificationCodeRepository
            ->expects($this->once())
            ->method('delete')
            ->with($verificationCode);

        $resultUser = $this->service->verifyPhone($phone, $code);

        $this->assertTrue($resultUser->isPhoneVerified());
    }

    public function testVerifyTelegramSuccess(): void
    {
        $telegramId = new TelegramId('123456789');
        $code = new VerificationCodeValue('123456');
        $user = User::registerNew(telegramId: $telegramId);
        $verificationCode = $this->createMock(VerificationCode::class);
        $verificationCode->method('isExpired')->willReturn(false);
        $verificationCode->method('matches')->with($code)->willReturn(true);
        $verificationCode->method('getUserId')->willReturn($user->getId());

        $this->userRepository
            ->expects($this->once())
            ->method('findByTelegramId')
            ->with($telegramId)
            ->willReturn($user);

        $this->verificationCodeRepository
            ->expects($this->once())
            ->method('findByTelegramIdAndCode')
            ->with($telegramId, $code)
            ->willReturn($verificationCode);

        $this->userRepository
            ->expects($this->once())
            ->method('save')
            ->with($user);

        $this->verificationCodeRepository
            ->expects($this->once())
            ->method('delete')
            ->with($verificationCode);

        $resultUser = $this->service->verifyTelegram($telegramId, $code);

        $this->assertTrue($resultUser->isTelegramVerified());
    }
}

