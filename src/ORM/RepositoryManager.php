<?php

namespace Nacho\ORM;

use Nacho\Contracts\DataHandlerInterface;
use Nacho\Helpers\DataHandler;
use Nacho\Nacho;

class RepositoryManager implements RepositoryManagerInterface
{
    private DataHandler $dataHandler;
    /** @var array|AbstractRepository[]|RepositoryInterface[] $repositories  */
    private array $repositories = [];

    public function __construct(DataHandlerInterface $dataHandler)
    {
        $this->dataHandler = $dataHandler;
    }

    public function getRepository(string $repositoryClass): RepositoryInterface
    {
        if (key_exists($repositoryClass, $this->repositories)) {
            return $this->repositories[$repositoryClass];
        }


        /** @var RepositoryInterface $repo */
        $repo = Nacho::$container->get($repositoryClass);
        $data = $this->dataHandler->readData($repo::getDataName());
        $repo->setData($data);
        $this->repositories[$repositoryClass] = $repo;

        return $repo;
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
            $this->dataHandler->storeAllData();
        }

        $this->repositories = [];
    }
}