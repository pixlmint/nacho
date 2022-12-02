<?php

namespace Nacho\ORM;

interface ModelInterface
{
    public static function init(array $data): self;
    public function toArray(): array;
}