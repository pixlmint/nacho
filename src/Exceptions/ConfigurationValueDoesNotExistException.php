<?php

namespace Nacho\Exceptions;

class ConfigurationValueDoesNotExistException extends \Exception
{
    public function __construct(string $configurationName, string $configurationKey)
    {
        parent::__construct("The configuration value '$configurationKey' does not exist in the configuration '$configurationName'.");
    }
}