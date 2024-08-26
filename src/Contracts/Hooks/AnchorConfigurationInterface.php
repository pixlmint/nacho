<?php

namespace Nacho\Contracts\Hooks;

interface AnchorConfigurationInterface
{
    public function exec($hook): void;
    public static function getInterface(): string;
    public static function getName(): string;
}
