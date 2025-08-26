<?php

namespace Nacho\Exceptions;

use Nacho\Models\HttpResponseCode;

class MethodNotAllowedHttpException extends BaseHttpException
{
    protected int $httpCode = HttpResponseCode::METHOD_NOT_ALLOWED;
}
