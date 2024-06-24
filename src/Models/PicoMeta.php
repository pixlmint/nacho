<?php

namespace Nacho\Models;

use Nacho\Contracts\ArrayableInterface;

class PicoMeta implements ArrayableInterface
{
    public string $title = '';
    public $time = null;
    public string $date = '';
    public string $security = '';
    public string $owner = '';
    public string $parentPath = '';
    public string $dateCreated = '';
    public string $dateUpdated = '';

    private ParameterBag $additionalValues;

    public function __construct(?array $data = [])
    {
        $this->additionalValues = new ParameterBag();
        foreach($data as $key => $value) {
            if (property_exists($this, $key) && !is_null($value)) {
                $this->$key = $value;
            } else {
                $this->additionalValues->set($key, $value);
            }
        }
    }

    public function __get(string $key): mixed
    {
        return $this->additionalValues->getOrNull($key);
    }

    public function __set(string $key, mixed $value): void
    {
        $this->additionalValues->set($key, $value);
    }

    public function getAdditionalValues(): ParameterBag
    {
        return $this->additionalValues;
    }

    public function toArray(): array
    {
        $ret = [];
        foreach (get_object_vars($this) as $key => $var) {
            if (!($var instanceof ParameterBag)) {
                $ret[$key] = $var;
            }
        }
        foreach ($this->additionalValues->keys() as $key) {
            $ret[$key] = $this->additionalValues->get($key);
        }

        return $ret;
    }
}