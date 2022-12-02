<?php

namespace Nacho\Security;

use Nacho\ORM\AbstractModel;
use Nacho\ORM\ModelInterface;

final class DefaultUser extends AbstractUser implements ModelInterface, UserInterface
{
    public static function init(array $data, int $id): ModelInterface
    {
        return new DefaultUser($id, $data['username'], $data['role'], $data['password'] ?: null);
    }
}