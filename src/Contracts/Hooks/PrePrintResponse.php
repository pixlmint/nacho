<?php

namespace Nacho\Contracts\Hooks;

interface PrePrintResponse
{
    public function call(string $response): string;
}