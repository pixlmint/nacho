<?php

namespace Nacho\Hooks;

class HookArgument
{
    private string $name;
    private $value;
    private bool $isRet;

    public function __construct(string $name, bool $isRet = false)
    {
        $this->name = $name;
        $this->isRet = $isRet;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getValue()
    {
        return $this->value;
    }

    public function setValue($value): void
    {
        $this->value = $value;
    }

    public function getIsRet(): bool
    {
        return $this->isRet;
    }
}
