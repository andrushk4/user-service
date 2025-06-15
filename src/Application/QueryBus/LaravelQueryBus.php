<?php

declare(strict_types=1);

namespace App\Application\QueryBus;

use Throwable;
use Illuminate\Contracts\Bus\Dispatcher as LaravelDispatcher;
use Illuminate\Contracts\Container\Container;

final readonly class LaravelQueryBus implements QueryBusInterface
{
    public function __construct(
        private LaravelDispatcher $dispatcher,
        private Container $container
    ) {}

    public function ask(object $query): mixed
    {
        $handlerClass = config('bus.map.' . get_class($query));

        if (!$handlerClass) {
            return $this->dispatcher->dispatch($query);
        }

        try {
            $handler = $this->container->make($handlerClass);

            return $handler->handle($query);
        } catch (Throwable $e) {
            throw $e;
        }
    }
}
