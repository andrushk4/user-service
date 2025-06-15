<?php

declare(strict_types=1);

namespace App\Domain\Entity\User\ValueObject;

use InvalidArgumentException;

final readonly class TelegramId
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
    private function validate(string $telegramId): void
    {
        // Telegram ID - это обычно число, но может быть представлен и строкой
        // Можно добавить более сложную валидацию, если нужно
        if (empty($telegramId) || !is_numeric($telegramId)) {
            throw new InvalidArgumentException('Некорректный Telegram ID.');
        }
    }
}
