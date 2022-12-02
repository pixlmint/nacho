<?php

namespace Nacho\Security;

use Nacho\ORM\AbstractModel;
use Nacho\ORM\ModelInterface;

class User extends AbstractModel implements ModelInterface
{
    private string $username;
    private string $role;
    private ?string $password;

    public static function init(array $data): ModelInterface
    {
        return new User($data['username'], $data['role'], $data['password'] ?: null);
    }

    public function __construct(string $username, string $role, ?string $password = null)
    {
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
}