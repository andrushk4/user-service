<?php

declare(strict_types=1);

namespace App\Application\QueryBus;

interface QueryBusInterface
{
    /**
     * Отправляет запрос и возвращает результат.
     */
    public function ask(object $query): mixed;
}
