<?php

namespace Nacho\Exceptions;

use Nacho\Models\HttpResponseCode;

class BadRequestHttpException extends BaseHttpException
{
    protected int $code = HttpResponseCode::BAD_REQUEST;
}

