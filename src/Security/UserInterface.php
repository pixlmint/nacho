<?php

namespace Nacho\Security;

interface UserInterface
{
    public function getRole(): string;
    public function setRole(string $role): void;
    public function getUsername(): string;
    public function getPassword(): string;
    public function setPassword(string $password): void;
}