<?php

namespace Nacho\ORM;

use Nacho\Contracts\ArrayableInterface;

abstract class AbstractModel implements ArrayableInterface
{
    protected int $id = -1;

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public static function init(TemporaryModel $data, int $id): ModelInterface
    {
        $str = static::class;
        $obj = new $str();
        $keys = array_keys($obj->toArray());
        foreach ($keys as $key) {
            $val = $data->get($key);
            if ($val instanceof TemporaryModel) {
                $val = $val->asArray();
            }
            if (property_exists($obj, $key)) {
                $obj->$key = $val;
            } elseif (method_exists($obj, 'set' . ucfirst($key))) {
                $setter = 'set' . ucfirst($key);
                $obj->$setter($val);
            }
        }
        $obj->setId($id);

        return $obj;
    }

    public function toArray(): array
    {
        $ret = [];
        foreach (get_object_vars($this) as $key => $var) {
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