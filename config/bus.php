<?php

return [
    'map' => [
        App\Application\Command\Registration\RegisterUserByEmailCommand::class => App\Application\Handler\Registration\RegisterUserByEmailHandler::class,
        App\Application\Command\Registration\RegisterUserByPhoneCommand::class => App\Application\Handler\Registration\RegisterUserByPhoneHandler::class,
        App\Application\Command\Registration\RegisterUserByTelegramCommand::class => App\Application\Handler\Registration\RegisterUserByTelegramHandler::class,

        App\Application\Command\Auth\LoginUserByEmailCommand::class => App\Application\Handler\Auth\LoginUserByEmailHandler::class,
        App\Application\Command\Auth\LoginUserByPhoneCommand::class => App\Application\Handler\Auth\LoginUserByPhoneHandler::class,
        App\Application\Command\Auth\LoginUserByTelegramCommand::class => App\Application\Handler\Auth\LoginUserByTelegramHandler::class,
        App\Application\Command\Auth\VerifyEmailCommand::class => App\Application\Handler\Auth\VerifyEmailHandler::class,
        App\Application\Command\Auth\VerifyPhoneCommand::class => App\Application\Handler\Auth\VerifyPhoneHandler::class,
        App\Application\Command\Auth\VerifyTelegramCommand::class => App\Application\Handler\Auth\VerifyTelegramHandler::class,
        App\Application\Command\Auth\RequestPasswordResetCommand::class => App\Application\Handler\Auth\RequestPasswordResetHandler::class,
        App\Application\Command\Auth\ResetPasswordCommand::class => App\Application\Handler\Auth\ResetPasswordHandler::class,

        App\Application\Query\Auth\GetUserByIdQuery::class => App\Application\QueryHandler\Auth\GetUserByIdQueryHandler::class,
    ],
    'middleware' => [
        //
    ],
];
