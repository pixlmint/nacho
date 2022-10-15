<?php

namespace Nacho\Models;

class PicoPage
{
    public string $id = '';
    public string $url = '';
    public bool $hidden = false;
    public string $raw_markdown = '';
    public string $raw_content = '';
    public ?PicoMeta $meta = null;
    public string $file = '';
    public ?string $content = null;

    public function __construct(?array $data = [])
    {
        $keys = array_keys(get_object_vars($this));
        foreach ($keys as $key) {
            $val = $data[$key] ?? null;
            if (is_object($val)) {
                $this->$key = $val;
            } elseif (!is_null($val)) {
                $this->$key = $val;
            }
        }
    }

    public function duplicate(): PicoPage
    {
        return new PicoPage((array) $this);
    }
}