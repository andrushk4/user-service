<?php

declare(strict_types=1);

namespace App\Application\Handler\Registration;

use InvalidArgumentException;
use App\Application\DTO\UserDTO;
use App\Domain\Service\UserRegistrationService;
use App\Domain\Entity\User\ValueObject\TelegramId;
use App\Application\Exception\UserAlreadyExistsException;
use App\Application\Command\Registration\RegisterUserByTelegramCommand;
use App\Domain\Entity\User\ValueObject\Password;

final readonly class RegisterUserByTelegramHandler
{
    public function __construct(
        private UserRegistrationService $userRegistrationService
    ) {}

    /**
     * @throws UserAlreadyExistsException
     */
    public function handle(RegisterUserByTelegramCommand $command): UserDTO
    {
        try {
            $telegramId = new TelegramId($command->telegramId);
            $password = new Password($command->password);

            $user = $this->userRegistrationService->registerWithTelegram(
                $telegramId,
                $password,
                $command->firstName,
                $command->lastName
            );

            return UserDTO::fromDomain($user);
        } catch (InvalidArgumentException $e) {
            if (str_contains($e->getMessage(), 'User with this Telegram ID already exists')) {
                throw new UserAlreadyExistsException('Пользователь с таким Telegram ID уже зарегистрирован.', 0, $e);
            }
            throw $e;
        }
    }
}
