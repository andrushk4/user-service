<?php

declare(strict_types=1);

namespace App\Domain\Service;

use InvalidArgumentException;
use App\Domain\Entity\User\User;
use App\Domain\Entity\User\ValueObject\Email;
use App\Domain\Entity\User\ValueObject\Phone;
use App\Domain\Exception\UserNotFoundException;
use App\Domain\Entity\User\ValueObject\Password;
use App\Domain\Entity\Credential\VerificationCode;
use App\Domain\Entity\User\ValueObject\TelegramId;
use App\Domain\Repository\UserRepositoryInterface;
use App\Domain\Exception\InvalidCredentialException;
use App\Infrastructure\Security\PasswordHasherInterface;
use App\Infrastructure\Notification\SMS\SMSGatewayInterface;
use App\Domain\Entity\User\ValueObject\VerificationCodeValue;
use App\Domain\Repository\VerificationCodeRepositoryInterface;
use App\Infrastructure\Notification\Email\EmailSenderInterface;
use App\Infrastructure\Notification\Telegram\TelegramClientInterface;
use Random\RandomException;

final readonly class UserRegistrationService
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private VerificationCodeRepositoryInterface $verificationCodeRepository,
        private PasswordHasherInterface $passwordHasher,
        private SMSGatewayInterface $smsGateway,
        private EmailSenderInterface $emailSender,
        private TelegramClientInterface $telegramClient
    )
    {
    }

    /**
     * Регистрация пользователя по Email.
     * Отправляет код верификации на Email.
     *
     * @throws InvalidArgumentException
     */
    public function registerWithEmail(Email $email, Password $plainPassword, ?string $firstName = null, ?string $lastName = null): User
    {
        if ($this->userRepository->findByEmail($email) !== null) {
            throw new InvalidArgumentException('Пользователь с таким email уже существует.');;
        }

        $hashedPassword = $this->passwordHasher->hash($plainPassword);
        $user = User::registerNew(
            email: $email,
            password: $hashedPassword,
            firstName: $firstName,
            lastName: $lastName
        );
        $this->userRepository->save($user);

        $this->sendEmailVerificationCode($user, $email);

        return $user;
    }

    /**
     * Регистрация пользователя по номеру телефона.
     * Отправляет код верификации по SMS.
     *
     * @throws InvalidArgumentException
     */
    public function registerWithPhone(Phone $phone, Password $plainPassword, ?string $firstName = null, ?string $lastName = null): User
    {
        if ($this->userRepository->findByPhone($phone) !== null) {
            throw new InvalidArgumentException('Пользователь с таким номером уже существует.');
        }

        $hashedPassword = $this->passwordHasher->hash($plainPassword);
        $user = User::registerNew(
            phone: $phone,
            password: $hashedPassword,
            firstName: $firstName,
            lastName: $lastName
        );
        $this->userRepository->save($user);

        $this->sendPhoneVerificationCode($user, $phone);

        return $user;
    }

    /**
     * Регистрация пользователя по Telegram ID.
     * Отправляет код верификации в Telegram.
     *
     * @throws InvalidArgumentException
     */
    public function registerWithTelegram(TelegramId $telegramId, Password $plainPassword, ?string $firstName = null, ?string $lastName = null): User
    {
        if ($this->userRepository->findByTelegramId($telegramId) !== null) {
            throw new InvalidArgumentException('Пользователь с таким Telegram ID уже существует.');
        }

        $hashedPassword = $this->passwordHasher->hash($plainPassword);
        $user = User::registerNew(
            telegramId: $telegramId,
            password: $hashedPassword,
            firstName: $firstName,
            lastName: $lastName
        );
        $this->userRepository->save($user);

        $this->sendTelegramVerificationCode($user, $telegramId);

        return $user;
    }

    /**
     * Верификация Email пользователя.
     *
     * @throws UserNotFoundException
     * @throws InvalidCredentialException
     */
    public function verifyEmail(Email $email, VerificationCodeValue $code): User
    {
        $user = $this->userRepository->findByEmail($email);
        if ($user === null) {
            throw new UserNotFoundException('Пользователь не найден.');
        }

        $verificationCode = $this->verificationCodeRepository->findByEmailAndCode($email, $code);

        if ($verificationCode === null || $verificationCode->isExpired() || !$verificationCode->matches($code)) {
            throw new InvalidCredentialException('Неверный код или истек срок его действия.');
        }

        if (!$user->getId()->equals($verificationCode->getUserId())) {
            throw new InvalidCredentialException('Неверный код.');
        }

        $user->markEmailAsVerified();
        $this->userRepository->save($user);
        $this->verificationCodeRepository->delete($verificationCode); // Удалить код после успешной верификации

        return $user;
    }

    /**
     * Верификация номера телефона пользователя.
     *
     * @throws UserNotFoundException
     * @throws InvalidCredentialException
     */
    public function verifyPhone(Phone $phone, VerificationCodeValue $code): User
    {
        $user = $this->userRepository->findByPhone($phone);
        if ($user === null) {
            throw new UserNotFoundException('Пользователь не найден.');
        }

        $verificationCode = $this->verificationCodeRepository->findByPhoneAndCode($phone, $code);

        if ($verificationCode === null || $verificationCode->isExpired() || !$verificationCode->matches($code)) {
            throw new InvalidCredentialException('Неверный код или истек срок его действия.');
        }

        if (!$user->getId()->equals($verificationCode->getUserId())) {
            throw new InvalidCredentialException('Неверный код.');
        }

        $user->markPhoneAsVerified();
        $this->userRepository->save($user);
        $this->verificationCodeRepository->delete($verificationCode);

        return $user;
    }

    /**
     * Верификация Telegram ID пользователя.
     *
     * @throws UserNotFoundException
     * @throws InvalidCredentialException
     */
    public function verifyTelegram(TelegramId $telegramId, VerificationCodeValue $code): User
    {
        $user = $this->userRepository->findByTelegramId($telegramId);
        if ($user === null) {
            throw new UserNotFoundException('Пользователь не найден.');
        }

        $verificationCode = $this->verificationCodeRepository->findByTelegramIdAndCode($telegramId, $code);

        if ($verificationCode === null || $verificationCode->isExpired() || !$verificationCode->matches($code)) {
            throw new InvalidCredentialException('Неверный код или истек срок его действия.');
        }

        if (!$user->getId()->equals($verificationCode->getUserId())) {
            throw new InvalidCredentialException('Неверный код.');
        }

        $user->markTelegramAsVerified();
        $this->userRepository->save($user);
        $this->verificationCodeRepository->delete($verificationCode);

        return $user;
    }

    /**
     * @throws RandomException
     */
    public function sendEmailVerificationCode(User $user, Email $email): void
    {
        $verificationCode = VerificationCode::createForEmail($user->getId(), $email);
        $this->verificationCodeRepository->save($verificationCode);
        $this->emailSender->sendVerificationEmail($email, $verificationCode->getCode());
    }

    /**
     * @throws RandomException
     */
    public function sendPhoneVerificationCode(User $user, Phone $phone): void
    {
        $verificationCode = VerificationCode::createForPhone($user->getId(), $phone);
        $this->verificationCodeRepository->save($verificationCode);
        $this->smsGateway->sendVerificationSMS($phone, $verificationCode->getCode());
    }

    /**
     * @throws RandomException
     */
    public function sendTelegramVerificationCode(User $user, TelegramId $telegramId): void
    {
        $verificationCode = VerificationCode::createForTelegram($user->getId(), $telegramId);
        $this->verificationCodeRepository->save($verificationCode);
        $this->telegramClient->sendVerificationCode($telegramId, $verificationCode->getCode());
    }
}
