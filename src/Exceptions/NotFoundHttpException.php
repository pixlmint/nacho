<?php

namespace Nacho\Exceptions;

use Nacho\Models\HttpResponseCode;

class NotFoundHttpException extends BaseHttpException
{
    protected int $httpCode = HttpResponseCode::NOT_FOUND;
}

