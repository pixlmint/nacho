<?php

namespace Nacho\Exceptions;

use Exception;

class ConfigurationDoesNotExistException extends Exception
{
    public function __construct(string $configName)
    {
        parent::__construct('The Configuration ' . $configName . ' does not exist');
    }
}