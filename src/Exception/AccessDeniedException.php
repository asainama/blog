<?php

namespace App\Exception;

use Exception;

class AccessDeniedException extends Exception
{
    public function __construct($message, $code)
    {
        parent::__construct($message, $code);
    }

    public function __toString()
    {
        $this->message;
    }
}
