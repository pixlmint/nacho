<?php

namespace Nacho\Helpers\AlternativeContentHandlers;

use Nacho\Contracts\AlternativeContentType;

class PDFContentType implements AlternativeContentType
{
    public static function rendererValue(): string
    {
        return 'pdf';
    }

    public static function mimeTypes(): array
    {
        return [
            'application/pdf',
        ];
    }
}