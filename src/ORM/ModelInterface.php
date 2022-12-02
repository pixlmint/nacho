<?php

namespace Nacho\ORM;

interface ModelInterface
{
    public static function init(array $data, int $id): self;
    public function getId(): int;
    public function toArray(): array;
}