<?php

declare(strict_types=1);

namespace App\Application\CommandBus;

use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\Container\Container;
use Illuminate\Contracts\Bus\Dispatcher as LaravelDispatcher;
use Throwable;

final readonly class LaravelCommandBus implements CommandBusInterface
{
    public function __construct(
        private LaravelDispatcher $dispatcher,
        private Container $container
    ) {}

    /**
     * @throws BindingResolutionException
     * @throws Throwable
     */
    public function dispatch(object $command): mixed
    {
        $handlerClass = config('bus.map.' . get_class($command));

        if (!$handlerClass) {
            return $this->dispatcher->dispatch($command);
        }

        try {
            $handler = $this->container->make($handlerClass);

            return $handler->handle($command);
        } catch (Throwable $e) {
            throw $e;
        }
    }
}
