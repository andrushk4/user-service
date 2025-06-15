<?php

declare(strict_types=1);

namespace App\Application\Exception;

class UserAlreadyExistsException extends ApplicationException
{
    protected $message = 'Пользователь с такими учетными данными уже существует.';
    protected $code = 409;
}
