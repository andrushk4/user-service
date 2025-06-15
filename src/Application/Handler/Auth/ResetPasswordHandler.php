<?php

declare(strict_types=1);

namespace App\Application\Handler\Auth;

use InvalidArgumentException;
use App\Application\DTO\UserDTO;
use App\Domain\Service\PasswordResetService;
use App\Domain\Entity\User\ValueObject\Email;
use App\Domain\Exception\UserNotFoundException;
use App\Domain\Entity\User\ValueObject\Password;
use App\Domain\Exception\InvalidCredentialException;
use App\Application\Command\Auth\ResetPasswordCommand;
use App\Application\Exception\VerificationFailedException;
use App\Domain\Entity\User\ValueObject\VerificationCodeValue;

final readonly class ResetPasswordHandler
{
    public function __construct(
        private PasswordResetService $passwordResetService
    ) {}

    /**
     * @throws VerificationFailedException
     */
    public function handle(ResetPasswordCommand $command): UserDTO
    {
        try {
            $email = new Email($command->email);
            $code = new VerificationCodeValue($command->code);
            $newPassword = new Password($command->newPassword);

            $user = $this->passwordResetService->resetPassword($email, $code, $newPassword);

            return UserDTO::fromDomain($user);
        } catch (UserNotFoundException $e) {
            throw new VerificationFailedException('Пользователь не найден.', 0, $e);
        } catch (InvalidCredentialException $e) {
            throw new VerificationFailedException('Неверный или истекший код сброса пароля.', 0, $e);
        } catch (InvalidArgumentException $e) {
            throw new VerificationFailedException('Неверный формат нового пароля: ' . $e->getMessage(), 0, $e);
        }
    }
}
