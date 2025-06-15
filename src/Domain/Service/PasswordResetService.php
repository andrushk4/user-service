<?php

declare(strict_types=1);

namespace App\Domain\Service;

use InvalidArgumentException;
use App\Domain\Entity\User\User;
use App\Domain\Entity\User\ValueObject\Email;
use App\Domain\Exception\UserNotFoundException;
use App\Domain\Entity\User\ValueObject\Password;
use App\Domain\Entity\Credential\VerificationCode;
use App\Domain\Repository\UserRepositoryInterface;
use App\Domain\Exception\InvalidCredentialException;
use App\Infrastructure\Security\PasswordHasherInterface;
use App\Domain\Entity\User\ValueObject\VerificationCodeValue;
use App\Domain\Repository\VerificationCodeRepositoryInterface;
use App\Infrastructure\Notification\Email\EmailSenderInterface;

final readonly class PasswordResetService
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private VerificationCodeRepositoryInterface $verificationCodeRepository,
        private PasswordHasherInterface $passwordHasher,
        private EmailSenderInterface $emailSender
    )
    {
    }

    /**
     * Запрос на сброс пароля. Отправляет код на Email пользователя.
     *
     * @throws UserNotFoundException
     * @throws InvalidArgumentException
     */
    public function requestPasswordReset(Email $email): void
    {
        $user = $this->userRepository->findByEmail($email);
        if ($user === null) {
            throw new UserNotFoundException('Пользователь не найден.');
        }
        if ($user->getEmail() === null) {
            throw new InvalidArgumentException('У пользователя нет email.');
        }

        $verificationCode = VerificationCode::createForEmail($user->getId(), $email, 1800);
        $this->verificationCodeRepository->save($verificationCode);

        $this->emailSender->sendPasswordResetEmail($email, $verificationCode->getCode());
    }

    /**
     * Проверка кода сброса пароля и установка нового пароля.
     *
     * @throws UserNotFoundException
     * @throws InvalidCredentialException
     */
    public function resetPassword(Email $email, VerificationCodeValue $code, Password $newPlainPassword): User
    {
        $user = $this->userRepository->findByEmail($email);
        if ($user === null) {
            throw new UserNotFoundException('Пользователь не найден.');
        }
        if ($user->getEmail() === null) {
            throw new InvalidArgumentException('У пользователя нет email.');
        }

        $verificationCode = $this->verificationCodeRepository->findByEmailAndCode($email, $code);

        if ($verificationCode === null || $verificationCode->isExpired() || !$verificationCode->matches($code)) {
            throw new InvalidCredentialException('Неверный код сброса пароля.');
        }

        if (!$user->getId()->equals($verificationCode->getUserId())) {
            throw new InvalidCredentialException('Неверный код сброса пароля.');
        }

        $hashedPassword = $this->passwordHasher->hash($newPlainPassword);
        $user->changePassword($hashedPassword);
        $this->userRepository->save($user);

        $this->verificationCodeRepository->delete($verificationCode);

        return $user;
    }
}
