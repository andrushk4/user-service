<?php

declare(strict_types=1);

namespace App\Infrastructure\Security;

use App\Domain\Entity\User\ValueObject\Password;
use App\Domain\Entity\User\ValueObject\HashedPassword;

interface PasswordHasherInterface
{
    public function hash(Password $plainPassword): HashedPassword;
    public function check(Password $plainPassword, HashedPassword $hashedPassword): bool;
    public function needsRehash(HashedPassword $hashedPassword): bool;
}
