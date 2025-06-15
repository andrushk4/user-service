<?php

declare(strict_types=1);

namespace App\Application\Query\Auth;

final readonly class GetUserByIdQuery
{
    public function __construct(public string $userId) {}
}
