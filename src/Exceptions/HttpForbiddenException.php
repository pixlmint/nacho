<?php

namespace Nacho\Exceptions;

class HttpForbiddenException extends HttpBaseException {
    public function __construct(string $message) {
        parent::__construct($message, 401);
    }
}

