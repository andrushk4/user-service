<?php

declare(strict_types=1);

namespace App\Application\Handler\Auth;

use App\Application\DTO\UserDTO;
use App\Application\DTO\AuthResultDTO;
use App\Domain\Exception\UserNotFoundException;
use App\Domain\Entity\User\ValueObject\Password;
use App\Domain\Service\UserAuthenticationService;
use App\Domain\Entity\User\ValueObject\TelegramId;
use App\Domain\Exception\InvalidCredentialException;
use App\Application\Command\Auth\LoginUserByTelegramCommand;
use App\Application\Exception\AuthenticationFailedException;
use App\Infrastructure\Security\AuthTokenGeneratorInterface;

final readonly class LoginUserByTelegramHandler
{
    public function __construct(
        private UserAuthenticationService $authenticationService,
        private AuthTokenGeneratorInterface $authTokenGenerator
    ) {}

    /**
     * @throws AuthenticationFailedException
     */
    public function handle(LoginUserByTelegramCommand $command): AuthResultDTO
    {
        try {
            $telegram = new TelegramId($command->telegramId);
            $password = new Password($command->password);

            $user = $this->authenticationService->authenticateWithTelegram($telegram, $password);

            $token = $this->authTokenGenerator->generateToken($user);

            return new AuthResultDTO(UserDTO::fromDomain($user), $token);
        } catch (UserNotFoundException|InvalidCredentialException $e) {
            throw new AuthenticationFailedException('Неверный телеграм или пароль.', 0, $e);
        }
    }
}
