<?php

namespace Nacho\Models;

use ArrayAccess;
use Nacho\Exceptions\UnknownParameterException;
use Nacho\Nacho;
use Psr\Log\LoggerInterface;

class ParameterBag implements ArrayAccess
{
    private array $values;

    public function __construct(array $values = [])
    {
        $this->values = $values;
    }

    public function set(mixed $key, mixed $value): void
    {
        $this->values[$key] = $value;
    }

    public function get(mixed $key): mixed
    {
        if (!$this->has($key)) {
            throw new UnknownParameterException($key, array_keys($this->values));
        }

        return $this->values[$key];
    }

    public function getOrNull(mixed $key): mixed
    {
        if (!$this->has($key)) {
            return null;
        }

        return $this->get($key);
    }

    public function remove(mixed $key): void
    {
        if ($this->has($key)) {
            unset($this->values[$key]);
        }
    }

    public function count(): int
    {
        return count($this->values);
    }

    public function has(mixed $key): bool
    {
        return key_exists($key, $this->values);
    }

    public function keys(): array
    {
        return array_keys($this->values);
    }

    /**
     * @deprecated
     * @param mixed $offset
     * @return bool
     */
    public function offsetExists(mixed $offset): bool
    {
        $this->logDeprecatedAccess();
        return $this->has($offset);
    }

    /**
     * @deprecated
     * @param mixed $offset
     * @return mixed
     */
    public function offsetGet(mixed $offset): mixed
    {
        $this->logDeprecatedAccess();
        return $this->get($offset);
    }

    /**
     * @deprecated
     * @param mixed $offset
     * @param mixed $value
     * @return void
     */
    public function offsetSet(mixed $offset, mixed $value): void
    {
        $this->logDeprecatedAccess();
        $this->set($offset, $value);
    }

    /**
     * @deprecated
     * @param mixed $offset
     * @return void
     */
    public function offsetUnset(mixed $offset): void
    {
        $this->logDeprecatedAccess();
        $this->remove($offset);
    }

    private function logDeprecatedAccess(): void
    {
        $logger = Nacho::$container->get(LoggerInterface::class);
        $backtrace = debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT, 2);
        $backtrace = $backtrace[count($backtrace) - 1];
        if (key_exists('file', $backtrace) && key_exists('function', $backtrace) && key_exists('line', $backtrace)) {
            $backtraceStr = $backtrace['file'] . '::' . $backtrace['function'] . ' at line ' . $backtrace['line'];
        } else {
            $backtraceStr = '(unable to determine backtrace)';
        }
        $logger->warning('Using array access on ParameterBag class. Backtrace: ' . $backtraceStr);
    }
}