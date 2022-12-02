<?php

namespace Nacho\Security;

use Nacho\ORM\AbstractModel;

abstract class AbstractUser extends AbstractModel
{
    protected string $username;
    protected string $role;
    protected ?string $password;

    public function __construct(int $id, string $username, string $role, ?string $password)
    {
        $this->id = $id;
        $this->username = $username;
        $this->role = $role;
        $this->password = $password;
    }

    public function toArray(): array
    {
        return [
            'username' => $this->username,
            'role' => $this->role,
            'password' => $this->password,
        ];
    }

    public function getRole(): string
    {
        return $this->role;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function getPassword(): string
    {
        return $this->password;
    }
}