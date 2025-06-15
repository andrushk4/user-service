<?php

declare(strict_types=1);

namespace App\Domain\Exception;

use Exception;

class InvalidCredentialException extends Exception
{
    protected $message = 'Invalid credentials.';
    protected $code = 401;
}
