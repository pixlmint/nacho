<?php

namespace Nacho\Exceptions;

abstract class BaseHttpException extends NachoException
{
    protected int $httpCode = -1;

    public function __construct(string $message = "")
    {
        parent::__construct($message ?? static::class, $this->httpCode);
    }
}

