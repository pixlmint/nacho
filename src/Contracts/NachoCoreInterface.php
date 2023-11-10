<?php

namespace Nacho\Contracts;

interface NachoCoreInterface
{
    public function init(array $containerConfig): void;
    public function run(): void;
}