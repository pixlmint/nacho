<?php

namespace Nacho\Models;

abstract class AbstractHttpResponse
{
    protected array $headers = [];

    public function setHeader($key, $value): void
    {
        $this->headers[$key] = $value;
    }

    public function getHeaders(): array
    {
        return $this->headers;
    }

    protected function sendHeaders(): void
    {
        foreach ($this->headers as $key => $value) {
            header("$key: $value");
        }
    }

    protected function sendStatus(int $status): void
    {
        header('HTTP/1.1 ' . $status);
    }
}