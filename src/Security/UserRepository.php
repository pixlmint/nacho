<?php

namespace Nacho\Security;

use Nacho\Exceptions\UserDoesNotExistException;
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
            if (is_array($user) && $user['username'] === $username) {
                return $this->initialiseObject($id);
            }
            if ($user instanceof UserInterface && $user->getUsername() === $username) {
                return $user;
            }
        }

        throw new UserDoesNotExistException($username);
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