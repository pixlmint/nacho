<?php

namespace Nacho\Security;

class UserRepository extends \Nacho\ORM\AbstractRepository implements \Nacho\ORM\RepositoryInterface
{
    public static function getDataName(): string
    {
        return 'users';
    }

    protected static function getModel(): string
    {
        return User::class;
    }
}