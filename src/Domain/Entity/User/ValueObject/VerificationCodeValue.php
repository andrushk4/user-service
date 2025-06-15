<?php

declare(strict_types=1);

namespace App\Domain\Entity\User\ValueObject;

use InvalidArgumentException;

final readonly class VerificationCodeValue
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
    private function validate(string $value): void
    {
        if (empty($value) || !ctype_digit($value) || strlen($value) !== 6) {
            throw new InvalidArgumentException('Код должен состоять из 6 цифр.');
        }
    }
}
