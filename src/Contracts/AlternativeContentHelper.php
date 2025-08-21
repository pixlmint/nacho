<?php

namespace Nacho\Contracts;

interface AlternativeContentHelper
{
    public function getContent(string $path): string;
}
