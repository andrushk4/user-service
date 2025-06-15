<?php

declare(strict_types=1);

namespace App\Application\Handler\Auth;

use InvalidArgumentException;
use App\Domain\Service\PasswordResetService;
use App\Domain\Entity\User\ValueObject\Email;
use App\Application\Command\Auth\RequestPasswordResetCommand;
use App\Application\Exception\UserNotFoundException as ApplicationUserNotFoundException;
use App\Domain\Exception\UserNotFoundException as DomainUserNotFoundException;
use RuntimeException;


final readonly class RequestPasswordResetHandler
{
    public function __construct(
        private PasswordResetService $passwordResetService
    ) {}

    /**
     * @throws ApplicationUserNotFoundException
     */
    public function handle(RequestPasswordResetCommand $command): void
    {
        try {
            $email = new Email($command->email);
            $this->passwordResetService->requestPasswordReset($email);
        } catch (DomainUserNotFoundException $e) {
            throw new ApplicationUserNotFoundException('Пользователь с таким email не найден.', 0, $e);
        } catch (InvalidArgumentException $e) {
            throw new RuntimeException('Произошла ошибка при запросе сброса пароля: ' . $e->getMessage(), 0, $e);
        }
    }
}
