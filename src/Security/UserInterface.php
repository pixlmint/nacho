<?php

namespace Nacho\Security;

interface UserInterface
{
    public function getRole(): string;
    public function getUsername(): string;
}