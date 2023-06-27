<?php

namespace Nacho\Exceptions;

class PasswordInvalidException extends \Exception
{
    public function __construct()
    {
        parent::__construct('The given Password is invalid');
    }
}