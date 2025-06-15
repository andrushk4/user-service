<?php

declare(strict_types=1);

namespace App\Application\CommandBus;

interface CommandBusInterface
{
    /**
     * Диспетчит команду и возвращает результат ее обработки.
     */
    public function dispatch(object $command): mixed;
}
