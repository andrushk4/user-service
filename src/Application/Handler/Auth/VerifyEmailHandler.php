<?php

declare(strict_types=1);

namespace App\Application\Handler\Auth;

use App\Application\DTO\UserDTO;
use App\Domain\Entity\User\ValueObject\Email;
use App\Domain\Exception\UserNotFoundException;
use App\Domain\Service\UserRegistrationService;
use App\Application\Command\Auth\VerifyEmailCommand;
use App\Domain\Exception\InvalidCredentialException;
use App\Application\Exception\VerificationFailedException;
use App\Domain\Entity\User\ValueObject\VerificationCodeValue;

final readonly class VerifyEmailHandler
{
    public function __construct(
        private UserRegistrationService $userRegistrationService
    ) {}

    /**
     * @throws VerificationFailedException
     */
    public function handle(VerifyEmailCommand $command): UserDTO
    {
        try {
            $email = new Email($command->email);
            $code = new VerificationCodeValue($command->code);

            $user = $this->userRegistrationService->verifyEmail($email, $code);

            return UserDTO::fromDomain($user);
        } catch (UserNotFoundException $e) {
            throw new VerificationFailedException('Пользователь не найден.', 0, $e);
        } catch (InvalidCredentialException $e) {
            throw new VerificationFailedException('Неверный или истекший код верификации Email.', 0, $e);
        }
    }
}
