<?php

namespace Nacho\Helpers;

class Utils
{
    public static function isJson($obj): bool
    {
        if (is_array($obj)) {
            return false;
        }
        json_decode($obj);
        return json_last_error() === JSON_ERROR_NONE;
    }
}
