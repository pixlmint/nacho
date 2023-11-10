<?php

namespace Nacho\Contracts;

interface RouteInterface
{
    public function getPath(): string;
    public function getController(): string;
    public function getFunction(): string;
    public function getVariables(): array;
}