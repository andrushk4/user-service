<?php

declare(strict_types=1);

namespace App\Application\Exception;

use Exception;

class UserNotFoundException extends Exception
{
    protected $message = 'Пользователь не найден.';
    protected $code = 404;
}
