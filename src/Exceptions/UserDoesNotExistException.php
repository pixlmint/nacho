<?php

namespace Nacho\Exceptions;

class UserDoesNotExistException extends \Exception
{
    public function __construct(string $username)
    {
        parent::__construct("Unable to find the user with username ${username}");
    }
}