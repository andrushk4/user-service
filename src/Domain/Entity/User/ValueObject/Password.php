<?php

declare(strict_types=1);

namespace App\Domain\Entity\User\ValueObject;

use InvalidArgumentException;

final readonly class Password
{
    public function __construct(public string $value)
    {
        $this->validate($value);
    }

    public function __toString(): string
    {
        return $this->value;
    }

    /**
     * @throws InvalidArgumentException
     */
    private function validate(string $password): void
    {
        // Простенькая валидация пароля
        if (strlen($password) < 8) {
            throw new InvalidArgumentException('Пароль должен содержать не менее 8 символов.');
        }
    }
}
