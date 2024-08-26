<?php

namespace Nacho\Exceptions;

use Exception;

abstract class HttpBaseException extends Exception {
    private int $httpErrorCode;
    public function __construct(string $message, int $code) {
        $this->httpErrorCode = $code;
        parent::__construct($message, $code);
    }

    public function getHttpErrorCode(): int {
        return $this->httpErrorCode;
    }
}

