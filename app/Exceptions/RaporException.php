<?php

namespace App\Exceptions;

use Exception;

class RaporException extends Exception
{
    protected $errorType;

    public function __construct($message, $errorType = null, $code = 0, Exception $previous = null)
    {
        parent::__construct($message, (int)$code, $previous);
        $this->errorType = $errorType;
    }

    public function getErrorType()
    {
        return $this->errorType;
    }
}