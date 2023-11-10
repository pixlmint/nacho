<?php

namespace Nacho\Security;

use Nacho\ORM\ModelInterface;
use Nacho\ORM\TemporaryModel;

final class DefaultUser extends AbstractUser implements ModelInterface, UserInterface
{
    public static function init(array|TemporaryModel $data, int $id): ModelInterface
    {
        return new DefaultUser($id, $data['username'], $data['role'], $data['password'] ?: null);
    }
}