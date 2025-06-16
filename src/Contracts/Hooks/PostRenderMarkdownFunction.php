<?php

namespace Nacho\Contracts\Hooks;

interface PostRenderMarkdownFunction
{
    public function call(string $content): string;
}
