<?php

namespace Nacho\ORM;

use Nacho\Contracts\DataHandlerInterface;
use Nacho\Nacho;

class RepositoryManager implements RepositoryManagerInterface
{
    private DataHandlerInterface $dataHandler;
    /** @var array|AbstractRepository[]|RepositoryInterface[] $repositories */
    private array $repositories = [];

    public function __construct(DataHandlerInterface $dataHandler)
    {
        $this->dataHandler = $dataHandler;
    }

    public function trackRepository(RepositoryInterface $repository): void
    {
        if (!key_exists($repository::class, $this->repositories)) {
            $this->repositories[$repository::class] = $repository;
            $data = $this->dataHandler->readData($repository::getDataName());
            $repository->setData($data);
        }
    }

    public function getRepository(string $repositoryClass): RepositoryInterface
    {
        if (key_exists($repositoryClass, $this->repositories)) {
            return $this->repositories[$repositoryClass];
        }

        return Nacho::$container->get($repositoryClass);
    }

    public function close(): void
    {
        $hasChanges = false;
        foreach ($this->repositories as $repo) {
            if ($repo->isDataChanged()) {
                $this->dataHandler->writeData($repo::getDataName(), $repo->getData());
                $hasChanges = true;
            }
        }
        if ($hasChanges) {
            $this->dataHandler->flush();
        }

        $this->repositories = [];
    }
}
