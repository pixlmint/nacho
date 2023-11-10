<?php

namespace Nacho\Contracts;

interface DataHandlerInterface
{
    public function writeData(string $dataType, array $data): void;
    public function storeAllData(): void;
    public function deleteElement(String $dataType, mixed $element): void;

}