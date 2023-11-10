<?php

namespace Nacho\Models;

use Exception;

class ContainerDefinitionsHolder
{
    private int $priority;
    private array $definitions;

    public function __construct(int $priority = 1, array $definitions = [])
    {
        $this->priority = $priority;
        $this->definitions = $definitions;
    }

    public function addDefinition(string $key, mixed $value): self
    {
        if (key_exists($key, $this->definitions)) {
            throw new Exception("Definition with key ${key} already exists");
        }
        $this->setDefinition($key, $value);
        return $this;
    }

    public function setDefinition(string $key, mixed $value): self
    {
        $this->definitions[$key] = $value;
        return $this;
    }

    public function getPriority(): int
    {
        return $this->priority;
    }

    public function getDefinitions(): array
    {
        return $this->definitions;
    }

}