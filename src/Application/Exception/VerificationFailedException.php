<?php

declare(strict_types=1);

namespace App\Application\Exception;

class VerificationFailedException extends ApplicationException
{
    protected $message = 'Код верификации недействителен или истек.';
    protected $code = 400;
}
