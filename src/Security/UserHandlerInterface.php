<?php


namespace Nacho\Security;


interface UserHandlerInterface
{
    public function getCurrentUser();

    public function getUsers();

    public function findUser(string $username);

    public function changePassword(string $username, string $oldPassword, string $newPassword);

    public function setPassword(string $username, string $newPassword);

    public function logout();

    public function passwordVerify(string $username, string $password);

    public function isGranted(string $minRight = 'Guest', ?array $user = null);

    public function modifyUser(string $username, string $key, $newVar);
}