<?php

namespace Nacho\Contracts;

interface Response
{
    public function getContent(): ?string;
    public function getStatus(): int;
    public function getHeaders(): array;
    public function send(): void;
}