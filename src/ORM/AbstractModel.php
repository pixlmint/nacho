<?php

namespace Nacho\ORM;

use Nacho\Contracts\ArrayableInterface;

abstract class AbstractModel implements ArrayableInterface
{
    protected int $id = -1;

    public function setId(int $id)
    {
        $this->id = $id;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public static function init(array $data, int $id): ModelInterface
    {
        $keys = array_keys(get_class_vars(static::class));
        $str = static::class;
        $obj = new $str();
        foreach ($keys as $key) {
            if (!key_exists($key, $data)) {
                throw new \Exception("Key ${key} does not exist in " . json_encode($data));
            }
            $obj->$key = $data[$key];
            $obj->setId($id);
        }

        return $obj;
    }

    public function toArray(): array
    {
        $ret = [];
        foreach (get_object_vars($this) as $key => $var) {
            print($key);
            if ($key === 'id') {
                continue;
            }
            if (is_object($var)) {
                if (($var instanceof ArrayableInterface) || ($var instanceof ModelInterface)) {
                    $ret[$key] = $var->toArray();
                } else {
                    $ret[$key] = (array) $var;
                }
            } else {
                $ret[$key] = $var;
            }
        }

        return $ret;
    }
}