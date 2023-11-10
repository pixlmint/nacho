<?php

namespace Nacho\Security;

use Nacho\Nacho;
use Nacho\Exceptions\UserDoesNotExistException;
use Nacho\Helpers\ConfigurationContainer;
use Nacho\ORM\AbstractRepository;
use Nacho\ORM\ModelInterface;
use Nacho\ORM\RepositoryInterface;

class UserRepository extends AbstractRepository implements RepositoryInterface
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
        $securityConfig = Nacho::$container->get(ConfigurationContainer::class)->getSecurity();
        if (key_exists('user_model', $securityConfig)) {
            return $securityConfig['user_model'];
        }

        return DefaultUser::class;
    }
}