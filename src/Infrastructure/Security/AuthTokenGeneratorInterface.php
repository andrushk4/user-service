<?php

declare(strict_types=1);

namespace App\Infrastructure\Security;

use App\Domain\Entity\User\User;

interface AuthTokenGeneratorInterface
{
    public function generateToken(User $user): string;
}
