<?php

namespace Nacho\Helpers;

class ServerVarsParser
{
    public static function parseHeaders(): array
    {
        $headers = [];
        foreach ($_SERVER as $key => $value) {
            $arrKey = explode('_', $key);
            if (array_shift($arrKey) === 'HTTP') {
                $headers[implode('_', $arrKey)] = $value;
            }
        }

        return $headers;
    }

    public static function getRequestMethod(): string
    {
        return strtoupper($_SERVER['REQUEST_METHOD']);
    }

    public static function getContentType(): ?string
    {
        return key_exists('CONTENT_TYPE', $_SERVER) ? $_SERVER['CONTENT_TYPE'] : null;
    }
}