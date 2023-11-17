<?php

namespace Nacho\ORM;

interface RepositoryInterface
{
    public static function getDataName(): string;
    public function setData(array $data): void;
    public function set(ModelInterface $newData): void;
    public function initialiseObject(int $id): ModelInterface;
    public function isDataChanged(): bool;
    public function getById(int $id): ?ModelInterface;
}