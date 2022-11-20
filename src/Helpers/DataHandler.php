<?php

namespace Nacho\Helpers;

use Nacho\Contracts\SingletonInterface;

class DataHandler implements SingletonInterface
{
    protected static ?DataHandler $instance = null;
    private array $data = [];

    public static function getInstance(): ?DataHandler
    {
         if (!static::$instance) {
             static::$instance = new DataHandler();
         }

         return static::$instance;
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

    public function writeData(string $dt, array $data): void
    {
        $this->data[$dt] = $data;
    }

    public function storeAllData(): void
    {
        foreach ($this->data as $dt => $arr) {
            $this->storeData($dt, $arr);
        }
    }

    protected function storeData(string $dt, array $data): void
    {
         file_put_contents(self::getFileName($dt), json_encode($data));
    }

    private function fetchData(string $dt): array
    {
        if (!is_file(self::getFileName($dt))) {
            throw new \Exception('The ' . $dt . ' file does not exist');
        }

        return json_decode(file_get_contents(self::getFileName($dt)));
    }

    protected static function getFileName(string $dt): string
    {
        return self::getDataDir() . $dt . '.json';
    }

    private function isDataCached(string $dt): bool
    {
        return key_exists($dt, $this->data);
    }
}