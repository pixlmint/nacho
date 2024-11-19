<?php

namespace Nacho\ORM;

interface RepositoryManagerInterface
{
    public function trackRepository(RepositoryInterface $repository): void;
    public function getRepository(string $repositoryClass): RepositoryInterface;
    public function close(): void;
}

