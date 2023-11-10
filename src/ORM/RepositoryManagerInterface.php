<?php

namespace Nacho\ORM;

interface RepositoryManagerInterface
{
    public function getRepository(string $repositoryClass): RepositoryInterface;
    public function close(): void;

}