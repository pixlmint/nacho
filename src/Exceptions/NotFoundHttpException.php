<?php

namespace Nacho\Exceptions;

use Nacho\Models\HttpResponseCode;

class NotFoundHttpException extends BaseHttpException
{
    protected int $code = HttpResponseCode::NOT_FOUND;
}

