<?php

declare(strict_types=1);

namespace App\Domain\Exception;

use Exception;

class UserAlreadyVerifiedException extends Exception
{
    protected $message = 'User already verified.';
    protected $code = 400;
}
