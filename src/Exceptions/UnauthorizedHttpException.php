<?php


namespace Nacho\Exceptions;

use Nacho\Models\HttpResponseCode;

class UnauthorizedHttpException extends BaseHttpException
{
    protected int $code = HttpResponseCode::UNAUTHORIZED;
}


