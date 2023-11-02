<?php

namespace Nacho\Models;

use Nacho\Contracts\Response;

class HttpResponse extends AbstractHttpResponse implements Response
{
    private ?string $content;
    private int $status;

    public function __construct(?string $content, int $status = 200, array $headers = [])
    {
        $this->content = $content;
        $this->status = $status;
        $this->headers = $headers;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function getStatus(): int
    {
        return $this->status;
    }

    public function send(): void {
        $this->sendHeaders();
        $this->sendStatus($this->getStatus());
        echo $this->getContent();
    }
}