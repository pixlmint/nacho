<?php

namespace Nacho\Contracts;

interface DataHandlerInterface
{
    public function writeData(string $dataType, array $data): void;
    public function deleteElement(String $dataType, mixed $element): void;

    /**
     * Write all cached data to persistent storage
     */
    public function flush(): void;
}
