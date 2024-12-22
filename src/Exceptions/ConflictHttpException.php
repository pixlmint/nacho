<?php

namespace Nacho\Exceptions;

use Nacho\Models\HttpResponseCode;

class ConflictHttpException extends BaseHttpException
{
    protected int $code = HttpResponseCode::CONFLICT;
}

