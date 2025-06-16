<?php

namespace Nacho\Contracts\Hooks;

interface PreRenderMarkdownFunction
{
    public function call(string $markdown): string;
}
