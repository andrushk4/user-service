<?php

declare(strict_types=1);

namespace App\Application\Exception;

class InvalidCredentialsException extends ApplicationException
{
    protected $message = 'Неверные учетные данные.';
    protected $code = 401;
}
