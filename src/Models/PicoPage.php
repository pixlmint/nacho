<?php

namespace Nacho\Models;

use Nacho\Contracts\ArrayableInterface;

class PicoPage implements ArrayableInterface
{
    public string $id = '';
    public string $url = '';
    public bool $hidden = false;
    public string $raw_markdown = '';
    public string $raw_content = '';
    public ?PicoMeta $meta = null;
    public string $file = '';
    public ?string $content = null;
    /** If PageManager.php has INCLUDE_PAGE_TREE set to true this will include this page's child pages */
    public ?array $children = null;

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

    public function setSecurity(string $security): void
    {
        $this->meta->security = $security;
    }

    public function getSecurity(): ?string
    {
        if ($this->meta->security) {
            return $this->meta->security;
        }

        return null;
    }

    public function duplicate(): PicoPage
    {
        $clone = new PicoPage((array)$this);
        $clone->meta = new PicoMeta($this->meta->toArray());
        return $clone;
    }

    public function toArray(): array
    {
        $ret = [];
        foreach (get_object_vars($this) as $key => $var) {
            if ($var instanceof ArrayableInterface) {
                $ret[$key] = $var->toArray();
            } elseif ($key === 'children' && is_array($var)) {
                $ret['children'] = [];
                foreach ($var as $child) {
                    $ret['children'][] = $child->toArray();
                }
            } else {
                $ret[$key] = $var;
            }
        }

        return $ret;
    }
}