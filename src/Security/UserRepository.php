<?php

namespace Nacho\Security;

use Nacho\Helpers\ConfigurationHelper;
use Nacho\ORM\ModelInterface;

class UserRepository extends \Nacho\ORM\AbstractRepository implements \Nacho\ORM\RepositoryInterface
{
    public static function getDataName(): string
    {
        return 'users';
    }

    public function getByUsername(string $username): ModelInterface
    {
        foreach ($this->getData() as $id => $user) {
            if ($user['username'] === $username) {
                return $this->initialiseObject($id);
            }
        }

        throw new \Exception("Unable to find the user with username ${username}");
    }

    protected static function getModel(): string
    {
        $securityConfig = ConfigurationHelper::getInstance()->getSecurity();
        if (key_exists('user_model', $securityConfig)) {
            return $securityConfig['user_model'];
        }

        return DefaultUser::class;
    }
}