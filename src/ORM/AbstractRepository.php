<?php

namespace Nacho\ORM;

use Nacho\Nacho;
use Psr\Log\LoggerInterface;

abstract class AbstractRepository implements RepositoryInterface
{
    /** @var array|ModelInterface[] $data */
    private array $data = [];
    private bool $dataChanged = false;
    protected LoggerInterface $logger;

    public function __construct()
    {
        $this->logger = Nacho::$container->get(LoggerInterface::class);
        $this->init();
    }

    private function init(): void
    {
        $manager = Nacho::$container->get(RepositoryManagerInterface::class);
        $manager->trackRepository($this);
        $className = static::class;
        $this->logger->info("Repository {$className} created");
    }

    public function setData(array $data): void
    {
        $this->data = $data;
    }

    public function set(ModelInterface $newData): void
    {
        $id = $newData->getId();
        $trackedEntity = array_filter($this->data, function ($entity) use ($newData) {
            return $entity === $newData;
        });

        if ($trackedEntity) {
            $this->dataChanged = true;
            return;
        }

        if ($id < 0 || !$id) {
            $this->data[] = $newData;
        } else {
            $this->data[$newData->getId()] = $newData;
        }
        $this->dataChanged = true;
    }

    public function setInitialized(int $id, ModelInterface $data): void
    {
        if (!key_exists($id, $this->data)) {
            throw new \Exception('Unknown Element with id ' . $id);
        }
        $this->data[$id] = $data;
    }

    public function initialiseObject(int $id): ModelInterface
    {
        $model = static::getModel();
        $obj = $model::init(new TemporaryModel($this->data[$id]), $id);
        $this->data[$id] = $obj;
        return $obj;
    }

    protected abstract static function getModel(): string;

    public function getData(): array
    {
        return $this->data;
    }

    public function isDataChanged(): bool
    {
        return $this->dataChanged;
    }

    // Get a specific object by its ID
    public function getById(int $id): ?ModelInterface
    {
        if (!key_exists($id, $this->data)) {
            return null;
        }
        if (is_array($this->data[$id])) {
            $this->data[$id] = $this->initialiseObject($id);
        }

        return $this->data[$id];
    }

    public function count(): int
    {
        $filtered = array_filter($this->data, function ($el) {
            return $el instanceof ModelInterface || is_array($el);
        });

        return count($filtered);
    }
}
