<?php

namespace Nacho\Contracts;

interface AlternativeContentType
{
    public static function rendererValue(): string;

    public static function mimeTypes(): array;
}