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
}