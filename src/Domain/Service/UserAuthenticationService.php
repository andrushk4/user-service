<?php

declare(strict_types=1);

namespace App\Domain\Service;

use App\Domain\Entity\User\User;
use App\Domain\Entity\User\ValueObject\Email;
use App\Domain\Entity\User\ValueObject\Phone;
use App\Domain\Exception\UserNotFoundException;
use App\Domain\Entity\User\ValueObject\Password;
use App\Domain\Entity\User\ValueObject\TelegramId;
use App\Domain\Repository\UserRepositoryInterface;
use App\Domain\Exception\InvalidCredentialException;
use App\Infrastructure\Security\PasswordHasherInterface;

final readonly class UserAuthenticationService
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private PasswordHasherInterface $passwordHasher,
    )
    {
    }

    /**
     * Аутентификация пользователя по Email и паролю.
     *
     * @throws UserNotFoundException
     * @throws InvalidCredentialException
     */
    public function authenticateWithEmail(Email $email, Password $plainPassword): User
    {
        $user = $this->userRepository->findByEmail($email);
        if ($user === null) {
            throw new UserNotFoundException('Пользователь не найден.');
        }

        if (!$this->passwordHasher->check($plainPassword, $user->getPassword())) {
            throw new InvalidCredentialException('Неверные учетные данные.');
        }

        if (!$user->isEmailVerified()) {
            throw new InvalidCredentialException('Аккаунт не верифицирован.');
        }

        return $user;
    }

    /**
     * Аутентификация пользователя по номеру телефона и паролю.
     *
     * @throws UserNotFoundException
     * @throws InvalidCredentialException
     */
    public function authenticateWithPhone(Phone $phone, Password $plainPassword): User
    {
        $user = $this->userRepository->findByPhone($phone);
        if ($user === null) {
            throw new UserNotFoundException('Пользователь не найден.');
        }

        if (!$user->isPhoneVerified()) {
            throw new InvalidCredentialException('Аккаунт не верифицирован.');
        }

        if (!$this->passwordHasher->check($plainPassword, $user->getPassword())) {
            throw new InvalidCredentialException('Неверные учетные данные.');
        }

        return $user;
    }

    /**
     * Аутентификация пользователя по Telegram ID и паролю.
     *
     * @throws UserNotFoundException
     * @throws InvalidCredentialException
     */
    public function authenticateWithTelegram(TelegramId $telegramId, Password $plainPassword): User
    {
        $user = $this->userRepository->findByTelegramId($telegramId);
        if ($user === null) {
            throw new UserNotFoundException('Пользователь не найден.');
        }

        if (!$user->isTelegramVerified()) {
            throw new InvalidCredentialException('Аккаунт не верифицирован.');
        }

        if (!$this->passwordHasher->check($plainPassword, $user->getPassword())) {
            throw new InvalidCredentialException('Неверные учетные данные.');
        }

        return $user;
    }
}
