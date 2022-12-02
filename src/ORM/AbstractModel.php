<?php

namespace Nacho\ORM;

use Nacho\Contracts\ArrayableInterface;

abstract class AbstractModel implements ArrayableInterface
{
    public static function init(array $data): ModelInterface
    {
        $keys = array_keys(get_class_vars(static::class));
        $str = static::class;
        $obj = new $str();
        foreach ($keys as $key) {
            if (!key_exists($key, $data)) {
                throw new \Exception("Key ${key} does not exist in " . json_encode($data));
            }
            $obj->$key = $data[$key];
        }

        return $obj;
    }

    public function toArray(): array
    {
        $ret = [];
        foreach (get_object_vars($this) as $key => $var) {
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