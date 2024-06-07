<?php

namespace Nacho\Hooks;

use Nacho\Nacho;

abstract class AbstractAnchor
{
    protected array $hooks = [];
    protected array $arguments = [];
    
    public function addHook(string $hook): void
    {
        $this->hooks[] = $hook;
    }

    public function run(array $args = [])
    {
        $this->populateArguments($args);
        foreach ($this->hooks as $hook) {
            $cls = Nacho::$container->get($hook);
            $this->exec($cls);
        }

        if ($this->getIsReturnVar() !== null) {
            return $this->arguments[$this->getIsReturnVar()]->getValue();
        }
    }

    public function hasHooks(): bool
    {
        return count($this->hooks) > 0;
    }

    protected function addArgument(string $name, bool $isReturnValue): void
    {
        $this->arguments[] = new HookArgument($name, $isReturnValue);
    }

    public abstract function exec(mixed $hook): void;

    private function populateArguments(array $args): void
    {
        foreach ($this->arguments as $i => $argument) {
            if (key_exists($argument->getName(), $args)) {
                $this->arguments[$i]->setValue($args[$argument->getName()]);
            }
        }
    }

    protected function getIsReturnVar(): ?int
    {
        foreach ($this->arguments as $i => $argument) {
            if ($argument->getIsRet()) {
                return $i;
            }
        }

        return null;
    }
}
