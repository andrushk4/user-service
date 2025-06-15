<?php

declare(strict_types=1);

namespace App\Infrastructure\Provider;

use Illuminate\Support\ServiceProvider;
use App\Domain\Service\PasswordResetService;
use App\Application\QueryBus\LaravelQueryBus;
use App\Application\QueryBus\QueryBusInterface;
use App\Domain\Service\UserRegistrationService;
use App\Application\CommandBus\LaravelCommandBus;
use App\Domain\Service\UserAuthenticationService;
use App\Domain\Repository\UserRepositoryInterface;
use App\Application\CommandBus\CommandBusInterface;
use App\Infrastructure\Notification\SMS\SMSGateway;
use App\Infrastructure\Security\BcryptPasswordHasher;
use App\Infrastructure\Notification\Email\EmailSender;
use App\Infrastructure\Security\SanctumTokenGenerator;
use App\Infrastructure\Security\PasswordHasherInterface;
use App\Infrastructure\Notification\SMS\SMSGatewayInterface;
use App\Infrastructure\Notification\Telegram\TelegramClient;
use App\Infrastructure\Security\AuthTokenGeneratorInterface;
use App\Domain\Repository\VerificationCodeRepositoryInterface;
use App\Infrastructure\Notification\Email\EmailSenderInterface;
use App\Infrastructure\Persistence\Eloquent\EloquentUserRepository;
use App\Infrastructure\Notification\Telegram\TelegramClientInterface;
use App\Infrastructure\Persistence\Redis\RedisVerificationCodeRepository;

class DomainServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(PasswordHasherInterface::class, BcryptPasswordHasher::class);
        $this->app->bind(AuthTokenGeneratorInterface::class, SanctumTokenGenerator::class);

        $this->app->bind(UserRepositoryInterface::class, EloquentUserRepository::class);
        $this->app->singleton(VerificationCodeRepositoryInterface::class, RedisVerificationCodeRepository::class);

        $this->app->bind(SMSGatewayInterface::class, SMSGateway::class);
        $this->app->bind(EmailSenderInterface::class, EmailSender::class);
        $this->app->bind(TelegramClientInterface::class, TelegramClient::class);

        $this->app->singleton(UserRegistrationService::class);
        $this->app->singleton(UserAuthenticationService::class);
        $this->app->singleton(PasswordResetService::class);

        $this->app->bind(CommandBusInterface::class, LaravelCommandBus::class);
        $this->app->bind(QueryBusInterface::class, LaravelQueryBus::class);
    }

    public function boot(): void
    {
        //
    }
}
