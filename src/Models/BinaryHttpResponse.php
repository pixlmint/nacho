<?php

namespace Nacho\Models;

class BinaryHttpResponse extends HttpResponse
{
    private ?string $filepath;

    public function __construct(?string $filepath = null, int $status = 200, array $headers = [])
    {
        parent::__construct('', $status, $headers);
        $this->filepath = $filepath;
    }

    public function setFilepath(string $filepath): void
    {
        $this->filepath = $filepath;
    }

    public function send(): void
    {
        if (is_null($this->filepath) && $this->getStatus() < 300) {
            throw new \Exception("No output file defined");
        }
        $this->sendHeaders();
        $this->sendStatus($this->getStatus());
        if (!is_null($this->filepath)) {
            readfile($this->filepath);
        }
    }
}
