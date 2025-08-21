<?php

namespace Nacho\Helpers\AlternativeContentHandlers;

use Nacho\Contracts\AlternativeContentType;

class JupyterNotebookContentType implements AlternativeContentType
{
    public static function rendererValue(): string
    {
        return 'ipynb';
    }

    public static function mimeTypes(): array
    {
        return [
            'application/json',
        ];
    }
}
