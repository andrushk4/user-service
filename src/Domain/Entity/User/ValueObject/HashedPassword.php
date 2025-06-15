<?php

declare(strict_types=1);

namespace App\Domain\Entity\User\ValueObject;

use InvalidArgumentException;

final readonly class HashedPassword
{
    public function __construct(public string $value)
    {
        $this->validate($value);
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
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
        // Простая проверка, что хэш не пустой
        if (empty($password)) {
            throw new InvalidArgumentException('Пароль не может быть пустым.');
        }
    }
}
