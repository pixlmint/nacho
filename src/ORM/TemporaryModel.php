<?php

namespace Nacho\ORM;

class TemporaryModel
{
    private array $data;

    public function __construct(array $data)
    {
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $data[$key] = new TemporaryModel($value);
            }
        }

        $this->data = $data;
    }

    public function get(string $key)
    {
        if (!key_exists($key, $this->data)) {
            $this->data[$key] = null;
        }

        return $this->data[$key];
    }

    public function asArray(): array
    {
        $ret = [];
        foreach ($this->data as $key => $value) {
            if ($value instanceof TemporaryModel) {
                $ret[$key] = $value->asArray();
            } else {
                $ret[$key] = $value;
            }
        }

        return $ret;
    }
}