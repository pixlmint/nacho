<?php

namespace Nacho\Helpers;

use InvalidArgumentException;

class UrlHelper
{
    public static function getPageUrl(string $page, $queryData = null, $dropIndex = true): string
    {
        if (!is_array($queryData)) {
            $queryData = [];
        }
        $queryData['p'] = $page;
        if (is_array($queryData)) {
            $queryData = http_build_query($queryData, '', '&');
        } elseif (($queryData !== null) && !is_string($queryData)) {
            throw new InvalidArgumentException(
                'Argument 2 passed to ' . __METHOD__ . ' must be of the type array or string, '
                . (is_object($queryData) ? get_class($queryData) : gettype($queryData)) . ' given'
            );
        }

        // drop "index"
        if ($dropIndex) {
            if ($page === 'index') {
                $page = '';
            } elseif (($pagePathLength = strrpos($page, '/')) !== false) {
                if (substr($page, $pagePathLength + 1) === 'index') {
                    $page = substr($page, 0, $pagePathLength);
                }
            }
        }

        if (!$queryData) {
            $queryData = '';
        }

        return UrlHelper::getBaseUrl() . $queryData;
    }

    public static function getBaseUrl(): string
    {
        $host = 'localhost';
        if (!empty($_SERVER['HTTP_X_FORWARDED_HOST'])) {
            $host = $_SERVER['HTTP_X_FORWARDED_HOST'];
        } elseif (!empty($_SERVER['HTTP_HOST'])) {
            $host = $_SERVER['HTTP_HOST'];
        } elseif (!empty($_SERVER['SERVER_NAME'])) {
            $host = $_SERVER['SERVER_NAME'];
        }

        $port = 80;
        if (!empty($_SERVER['HTTP_X_FORWARDED_PORT'])) {
            $port = (int)$_SERVER['HTTP_X_FORWARDED_PORT'];
        } elseif (!empty($_SERVER['SERVER_PORT'])) {
            $port = (int)$_SERVER['SERVER_PORT'];
        }

        $hostPortPosition = ($host[0] === '[') ? strpos($host, ':', strrpos($host, ']') ?: 0) : strrpos($host, ':');
        if ($hostPortPosition !== false) {
            $port = (int)substr($host, $hostPortPosition + 1);
            $host = substr($host, 0, $hostPortPosition);
        }

        $protocol = 'http';
        if (!empty($_SERVER['HTTP_X_FORWARDED_PROTO'])) {
            $secureProxyHeader = strtolower(current(explode(',', $_SERVER['HTTP_X_FORWARDED_PROTO'])));
            $protocol = in_array($secureProxyHeader, array('https', 'on', 'ssl', '1'), true) ? 'https' : 'http';
        } elseif (!empty($_SERVER['HTTPS']) && ($_SERVER['HTTPS'] !== 'off')) {
            $protocol = 'https';
        } elseif ($port === 443) {
            $protocol = 'https';
        }

        $basePath = isset($_SERVER['SCRIPT_NAME']) ? dirname($_SERVER['SCRIPT_NAME']) : '/';
        $basePath = !in_array($basePath, array('.', '/', '\\'), true) ? $basePath . '/' : '/';

        if ((($protocol === 'http') && ($port !== 80)) || (($protocol === 'https') && ($port !== 443))) {
            $host = $host . ':' . $port;
        }

        return $protocol . "://" . $host . $basePath;
    }
}