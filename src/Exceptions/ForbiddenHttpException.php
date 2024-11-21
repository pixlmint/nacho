<?php

namespace Nacho\Exceptions;

use Nacho\Models\HttpResponseCode;

class ForbiddenHttpException extends BaseHttpException
{
    protected int $code = HttpResponseCode::FORBIDDEN;
}

