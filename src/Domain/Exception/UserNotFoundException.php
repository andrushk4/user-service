<?php

declare(strict_types=1);

namespace App\Domain\Exception;

use Exception;

class UserNotFoundException extends Exception
{
    protected $message = 'User not found.';
    protected $code = 404;
}
