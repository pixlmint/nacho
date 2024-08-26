<?php

namespace Nacho\Helpers;

use Nacho\Contracts\ArrayableInterface;
use Nacho\Contracts\DataHandlerInterface;
use Nacho\Nacho;
use Psr\Log\LoggerInterface;

class DataHandler implements DataHandlerInterface
{
    private array $data = [];
    private LoggerInterface $logger;

    public function __construct()
    {
        $this->logger = Nacho::$container->get(LoggerInterface::class);
    }

    public static function getDataDir(): string
    {
        return $_SERVER['DOCUMENT_ROOT'] . '/data';
    }

    public function readData(string $dt): array
    {
        if (!$this->isDataCached($dt)) {
            $this->data[$dt] = $this->fetchData($dt);
        }

        return $this->data[$dt];
    }

    public function writeData(string $dataType, array $data): void
    {
        $this->data[$dataType] = $data;
    }

    public function storeAllData(): void
    {
        foreach ($this->data as $dt => $arr) {
            $this->storeData($dt, $arr);
        }
    }

    public function deleteElement(string $dataType, $element): void
    {
        $data = $this->readData($dataType);
        if (!in_array($element, $data)) {
            return;
        }
        $index = array_search($element, $data);
        unset($data[$index]);
        $this->writeData($dataType, $data);
    }

    public function addElement(string $dt, $element): void
    {
        $data = $this->readData($dt);
        $data[] = $element;
        $this->writeData($dt, $data);
    }

    protected function storeData(string $dt, array $data): void
    {
        file_put_contents(self::getFileName($dt), json_encode(static::serializeData($data)));
        $entryCount = count($data);
        $this->logger->info("Stored $entryCount entries in file " . self::getFileName($dt));
    }

    protected static function serializeData(array $data): array
    {
        return array_map(function ($el) {
            if ($el instanceof ArrayableInterface) {
                return $el->toArray();
            }
            if (is_array($el)) {
                return $el;
            }
            throw new \Exception("Unable to serialize an element. Please either make it an array or have it implement the ArrayableInterface interface");
        }, $data);
    }

    private function fetchData(string $dt): array
    {
        if (!is_file(self::getFileName($dt))) {
            return [];
        }
        $this->logger->info("Reading data from file " . self::getFileName($dt));

        return json_decode(file_get_contents(self::getFileName($dt)), true);
    }

    protected static function getFileName(string $dt): string
    {
        return self::getDataDir() . DIRECTORY_SEPARATOR . $dt . '.json';
    }

    private function isDataCached(string $dt): bool
    {
        return key_exists($dt, $this->data);
    }
}
