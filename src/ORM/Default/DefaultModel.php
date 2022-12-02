<?php

namespace Nacho\ORM\Default;

use Nacho\ORM\ModelInterface;
use Nacho\ORM\AbstractModel;

class DefaultModel extends AbstractModel implements ModelInterface
{
    public static function init(array $data): ModelInterface
    {
        $obj = new DefaultModel();
        foreach ($data as $key => $value) {
            $obj->$key = $value;
        }

        return $obj;
    }
}