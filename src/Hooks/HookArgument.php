<?php

namespace Nacho\Hooks;

class HookArgument
{
    private string $name;
    private mixed $value;
    private bool $isRet;

    public function __construct(string $name, mixed $value, bool $isRet = false)
    {
        $this->name = $name;
        $this->value = $value;
        $this->isRet = $isRet;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getValue(): mixed
    {
        return $this->value;
    }

    public function setValue(mixed $value): void
    {
        $this->value = $value;
    }

    public function getIsRet(): bool
    {
        return $this->isRet;
    }
}
