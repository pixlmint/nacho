<?php

namespace Nacho\Contracts\Hooks;

interface PreRenderTemplate
{
    public function call(string $template, array $parameters): array;
}
