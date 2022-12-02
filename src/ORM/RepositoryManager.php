<?php

namespace Nacho\ORM;

use Nacho\Contracts\SingletonInterface;
use Nacho\Helpers\DataHandler;

class RepositoryManager implements SingletonInterface
{
    private static ?SingletonInterface $instance = null;
    private DataHandler $dataHandler;
    /** @var array|AbstractRepository[]|RepositoryInterface[] $repositories  */
    private array $repositories = [];

    public function __construct()
    {
        $this->dataHandler = new DataHandler();
    }

    public function getRepository(string $repoClass): RepositoryInterface
    {
        if (key_exists($repoClass, $this->repositories)) {
            return $this->repositories[$repoClass];
        }

        /** @var RepositoryInterface $repo */
        $repo = new $repoClass();
        $data = $this->dataHandler->readData($repo::getDataName());
        $repo->setData($data);
        $this->repositories[$repoClass] = $repo;

        return $repo;
    }

    public function close(): void
    {
        foreach ($this->repositories as $repo) {
            if ($repo->isDataChanged()) {
                $this->dataHandler->writeData($repo::getDataName(), $repo->getData());
            }
        }

        $this->repositories = [];
    }

    public static function getInstance(): self
    {
        if (!self::$instance) {
            self::$instance = new RepositoryManager();
        }

        return self::$instance;
    }
}