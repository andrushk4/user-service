<?php

declare(strict_types=1);

namespace App\Application\Handler\Auth;

use App\Application\DTO\UserDTO;
use App\Domain\Exception\UserNotFoundException;
use App\Domain\Service\UserRegistrationService;
use App\Domain\Entity\User\ValueObject\TelegramId;
use App\Domain\Exception\InvalidCredentialException;
use App\Application\Command\Auth\VerifyTelegramCommand;
use App\Application\Exception\VerificationFailedException;
use App\Domain\Entity\User\ValueObject\VerificationCodeValue;

final readonly class VerifyTelegramHandler
{
    public function __construct(
        private UserRegistrationService $userRegistrationService
    ) {}

    public function handle(VerifyTelegramCommand $command): UserDTO
    {
        try {
            $telegramId = new TelegramId($command->telegramId);
            $code = new VerificationCodeValue($command->code);

            $user = $this->userRegistrationService->verifyTelegram($telegramId, $code);

            return UserDTO::fromDomain($user);
        } catch (UserNotFoundException $e) {
            throw new VerificationFailedException('Пользователь не найден.', 0, $e);
        } catch (InvalidCredentialException $e) {
            throw new VerificationFailedException('Неверный или истекший код верификации Telegram.', 0, $e);
        }
    }
}
