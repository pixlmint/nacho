<?php

namespace Nacho\ORM;

use App\Models\Person;

abstract class AbstractRepository
{
    /** @var array|ModelInterface[] $data */
    private array $data = [];
    private bool $dataChanged = false;

    public function setData(array $data): void
    {
        $this->data = $data;
    }

    public function set(ModelInterface $newData): void
    {
        $this->data[$newData->getId()] = $newData;
        $this->dataChanged = true;
    }

    public function initialiseObject(int $id): ModelInterface
    {
        $model = static::getModel();
        $obj = $model::init($this->data[$id], $id);
        return $obj;
    }

    protected static function getModel(): string
    {
        return '';
    }

    public function getData(): array
    {
        return $this->data;
    }

    public function isDataChanged(): bool
    {
        return $this->dataChanged;
    }

    // Get a specific object by its ID
    public function getById(int $id): ModelInterface
    {
        if (is_array($this->data[$id])) {
            $this->data[$id] = $this->initialiseObject($id);
        }

        return $this->data[$id];
    }
}