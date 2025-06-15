<?php

declare(strict_types=1);

namespace App\Application\QueryHandler\Auth;

use Ramsey\Uuid\Uuid;
use App\Application\DTO\UserDTO;
use App\Application\Query\Auth\GetUserByIdQuery;
use App\Domain\Repository\UserRepositoryInterface;
use App\Application\Exception\ApplicationException;
use Ramsey\Uuid\Exception\InvalidUuidStringException;

final readonly class GetUserByIdQueryHandler
{
    public function __construct(
        private UserRepositoryInterface $userRepository
    ) {}

    /**
     * @throws ApplicationException
     */
    public function handle(GetUserByIdQuery $query): UserDTO
    {
        try {
            $uuid = Uuid::fromString($query->userId);
            $user = $this->userRepository->findById($uuid);

            if ($user === null) {
                throw new ApplicationException('Пользователь не найден.', 404);
            }

            return UserDTO::fromDomain($user);
        } catch (InvalidUuidStringException $e) {
            throw new ApplicationException('Неверный формат UUID пользователя.', 400, $e);
        }
    }
}
