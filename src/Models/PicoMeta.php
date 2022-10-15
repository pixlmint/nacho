<?php

namespace Nacho\Models;

class PicoMeta
{
    public string $title = '';
    public $time = null;
    public string $date = '';

    public function __construct(?array $data = [])
    {
        foreach($data as $key => $value) {
            if (!is_null($value)) {
                $this->$key = $value;
            }
        }
    }
}