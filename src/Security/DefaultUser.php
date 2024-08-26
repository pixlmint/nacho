<?php

namespace Nacho\Security;

use Nacho\ORM\ModelInterface;
use Nacho\ORM\TemporaryModel;

final class DefaultUser extends AbstractUser implements ModelInterface, UserInterface
{
    public static function init($data, int $id): ModelInterface
    {
        if ($data instanceof TemporaryModel) {
            return new DefaultUser($id, $data->get('username'), $data->get('role'), $data->get('password') ?: null);
        } else {
            return new DefaultUser($id, $data['username'], $data['role'], $data['password'] ?: null);
        }
    }
}
