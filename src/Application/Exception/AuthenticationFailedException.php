<?php

declare(strict_types=1);

namespace App\Application\Exception;

class AuthenticationFailedException extends ApplicationException
{
    protected $message = 'Неверные учетные данные или аккаунт не верифицирован.';
    protected $code = 401;
}
