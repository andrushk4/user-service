<?php

declare(strict_types=1);

namespace App\Domain\Entity\User\ValueObject;

use InvalidArgumentException;

final readonly class Phone
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
    private function validate(string $phone): void
    {
        if (!preg_match('/^\+[1-9]\d{1,14}$/', $phone)) {
            throw new InvalidArgumentException('Некорректный номер телефона. Должен быть в формате +123456789012345.');
        }
    }
}
