<?php

declare(strict_types=1);

namespace App\Application\Handler\Registration;

use InvalidArgumentException;
use App\Application\DTO\UserDTO;
use App\Domain\Entity\User\ValueObject\Phone;
use App\Domain\Service\UserRegistrationService;
use App\Domain\Entity\User\ValueObject\Password;
use App\Application\Exception\UserAlreadyExistsException;
use App\Application\Command\Registration\RegisterUserByPhoneCommand;

final readonly class RegisterUserByPhoneHandler
{
    public function __construct(
        private UserRegistrationService $userRegistrationService
    ) {}

    /**
     * @throws UserAlreadyExistsException
     */
    public function handle(RegisterUserByPhoneCommand $command): UserDTO
    {
        try {
            $phone = new Phone($command->phone);
            $password = new Password($command->password);

            $user = $this->userRegistrationService->registerWithPhone(
                $phone,
                $password,
                $command->firstName,
                $command->lastName
            );

            return UserDTO::fromDomain($user);
        } catch (InvalidArgumentException $e) {
            if (str_contains($e->getMessage(), 'User with this phone number already exists')) {
                throw new UserAlreadyExistsException('Пользователь с таким номером телефона уже зарегистрирован.', 0, $e);
            }
            throw $e;
        }
    }
}
