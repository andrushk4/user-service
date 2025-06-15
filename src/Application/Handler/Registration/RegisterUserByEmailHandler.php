<?php

declare(strict_types=1);

namespace App\Application\Handler\Registration;

use App\Application\DTO\UserDTO;
use App\Domain\Entity\User\ValueObject\Email;
use App\Domain\Service\UserRegistrationService;
use App\Domain\Entity\User\ValueObject\Password;
use App\Application\Exception\UserAlreadyExistsException;
use App\Application\Command\Registration\RegisterUserByEmailCommand;
use InvalidArgumentException;

final readonly class RegisterUserByEmailHandler
{
    public function __construct(
        private UserRegistrationService $userRegistrationService
    ) {}

    /**
     * @throws UserAlreadyExistsException
     */
    public function handle(RegisterUserByEmailCommand $command): UserDTO
    {
        try {
            $email = new Email($command->email);
            $password = new Password($command->password);

            $user = $this->userRegistrationService->registerWithEmail(
                $email,
                $password,
                $command->firstName,
                $command->lastName
            );

            return UserDTO::fromDomain($user);
        } catch (InvalidArgumentException $e) {
            if (str_contains($e->getMessage(), 'User with this email already exists')) {
                throw new UserAlreadyExistsException('Пользователь с таким email уже зарегистрирован.', 0, $e);
            }
            throw $e;
        }
    }
}
