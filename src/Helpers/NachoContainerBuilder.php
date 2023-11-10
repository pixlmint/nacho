<?php

namespace Nacho\Helpers;

use DI\Container;
use DI\ContainerBuilder;
use Nacho\Models\ContainerDefinitionsHolder;

class NachoContainerBuilder
{
    private ContainerBuilder $builder;
    /** @var array|ContainerDefinitionsHolder[] $definitions */
    private array $definitions = [];

    public function __construct() {
        $this->builder = new ContainerBuilder();
    }

    public function build(): Container
    {
        $this->builder->addDefinitions($this->getMergedDefinitions());
        return $this->builder->build();
    }

    public function enableCompilation(string $cacheDir): self
    {
        $this->builder->enableCompilation($cacheDir);
        return $this;
    }

    public function useAutowiring(bool $useAutowiring): self
    {
        $this->builder->useAutowiring($useAutowiring);
        return $this;
    }

    public function useAttributes(bool $useAttributes = true): self
    {
        $this->builder->useAttributes($useAttributes);
        return $this;
    }

    public function enableDefinitionCache(string $cacheDir): self
    {
        $this->builder->enableDefinitionCache($cacheDir);
        return $this;
    }

    public function isCompilationEnabled(): bool
    {
        return $this->builder->isCompilationEnabled();
    }

    public function addDefinitions(ContainerDefinitionsHolder $definitions): self
    {
        $this->definitions[] = $definitions;
        return $this;
    }

    public function getMergedDefinitions(): array
    {
        $sortedDefinitions = $this->definitions;
        $mergedDefinitions = [];
        usort($sortedDefinitions, function (ContainerDefinitionsHolder $a, ContainerDefinitionsHolder $b) {
            return $a->getPriority() <=> $b->getPriority();
        });
        foreach ($sortedDefinitions as $definition) {
            $mergedDefinitions = array_merge($mergedDefinitions, $definition->getDefinitions());
        }
        return $mergedDefinitions;
    }

}