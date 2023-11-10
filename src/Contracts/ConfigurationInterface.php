<?php

namespace Nacho\Contracts;

interface ConfigurationInterface
{
    public static function getConfigName(): string;
    public function get(string $key);
}