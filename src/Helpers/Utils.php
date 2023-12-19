<?php

namespace Nacho\Helpers;

class Utils
{
    public static function isJson(mixed $obj): bool
    {
        json_decode($obj);
        return json_last_error() === JSON_ERROR_NONE;
    }
}