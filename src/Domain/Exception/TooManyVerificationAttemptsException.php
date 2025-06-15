<?php

declare(strict_types=1);

namespace App\Domain\Exception;

use Exception;

class TooManyVerificationAttemptsException extends Exception
{
    protected $message = 'Too many verification attempts.';
    protected $code = 429;
}
