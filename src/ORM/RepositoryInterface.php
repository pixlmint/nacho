<?php

namespace Nacho\ORM;

interface RepositoryInterface
{
    public static function getDataName(): string;
    public function setData(array $data): void;
}