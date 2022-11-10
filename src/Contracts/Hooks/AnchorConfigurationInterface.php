<?php

namespace Nacho\Contracts\Hooks;

interface AnchorConfigurationInterface
{
    public function exec(mixed $hook): void;
    public static function getInterface(): string;
    public static function getName(): string;
}