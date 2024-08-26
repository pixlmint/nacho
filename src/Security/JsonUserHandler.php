<?php

namespace Nacho\Security;

use Nacho\Contracts\UserHandlerInterface;
use Nacho\Exceptions\PasswordInvalidException;
use Nacho\Helpers\DataHandler;
use Nacho\Nacho;
use Nacho\ORM\ModelInterface;
use Nacho\ORM\RepositoryInterface;
use Nacho\ORM\RepositoryManager;
use Nacho\ORM\RepositoryManagerInterface;

class JsonUserHandler implements UserHandlerInterface
{
    protected UserRepository $userRepository;

    public function __construct()
    {
        if (!isset($_SESSION['user'])) {
            $_SESSION['user'] = ['username' => 'Guest', 'password' => null, 'role' => 'Guest'];
        }
        $this->userRepository = Nacho::$container->get(UserRepository::class);
    }

    public function getCurrentUser()
    {
        return $this->findUser($_SESSION['user']['username']);
    }

    public function getUsers(): array
    {
        return $this->userRepository->getData();
    }

    /**
     * @throws PasswordInvalidException
     */
    public function changePassword(string $username, string $oldPassword, string $newPassword): bool
    {
        $user = $this->findUser($username);
        if (!$this->passwordVerify($user, $oldPassword)) {
            throw new PasswordInvalidException();
        }

        $this->setPassword($username, $newPassword);

        return true;
    }

    public function passwordVerify(UserInterface $user, string $password): bool
    {
        return password_verify($password, $user->getPassword());
    }

    public function setPassword(string $username, string $newPassword): UserInterface
    {
        $user = $this->findUser($username);
        $user->setPassword(password_hash($newPassword, PASSWORD_DEFAULT));
        $this->userRepository->set($user);

        return $user;
    }

    public function findUser($username)
    {
        return $this->userRepository->getByUsername($username);
    }

    public function logout(): void
    {
        session_destroy();
    }

    public function getRoles(): array
    {
        return ['Super Admin', 'Editor', 'Reader', 'Guest'];
    }

    public function isGranted(string $minRight = 'Guest', ?UserInterface $user = null): bool
    {
        if (!$user) {
            $user = $this->getCurrentUser();
        }

        return array_search($user->getRole(), $this->getRoles()) <= array_search($minRight, $this->getRoles());
    }
}
