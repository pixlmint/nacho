<?php

namespace Nacho\Exceptions;

use Nacho\Models\HttpResponseCode;

class BadRequestHttpException extends BaseHttpException
{
    protected int $httpCode = HttpResponseCode::BAD_REQUEST;
}

