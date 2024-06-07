<?php

namespace Nacho\Exceptions;

use Exception;

class UnknownParameterException extends Exception
{
    const MAX_KEYS_TO_PRINT = 20;

    public function __construct(mixed $key, array $availableKeys)
    {
        if (count($availableKeys) > self::MAX_KEYS_TO_PRINT) {
            $availableKeysString = sprintf("(%d keys, omitted due to amount)", count($availableKeys));
        } else {
            $availableKeysString = implode(', ', $availableKeys);
        }

        parent::__construct(sprintf("The parameter with key %s is not defined. Available keys: %s", $key, $availableKeysString));
    }

}