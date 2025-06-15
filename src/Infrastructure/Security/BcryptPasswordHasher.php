<?php

declare(strict_types=1);

namespace App\Infrastructure\Security;

use App\Domain\Entity\User\ValueObject\Password;
use App\Domain\Entity\User\ValueObject\HashedPassword;

final readonly class BcryptPasswordHasher implements PasswordHasherInterface
{
    public function hash(Password $plainPassword): HashedPassword
    {
        return new HashedPassword(password_hash($plainPassword->value, PASSWORD_BCRYPT));
    }

    public function check(Password $plainPassword, HashedPassword $hashedPassword): bool
    {
        return password_verify($plainPassword->value, $hashedPassword->value);
    }

    public function needsRehash(HashedPassword $hashedPassword): bool
    {
        return password_needs_rehash($hashedPassword->value, PASSWORD_BCRYPT);
    }
}
