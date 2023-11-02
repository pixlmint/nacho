<?php

namespace Nacho\Models;

use Nacho\Contracts\Response;

class HttpRedirectResponse extends AbstractHttpResponse implements Response
{
    private bool $isPermanent;

    public function __construct(string $location, bool $isPermanent = false)
    {
        $this->isPermanent = $isPermanent;
        $this->headers['Location'] = $location;
    }

    public function getContent(): ?string
    {
        return null;
    }

    public function getStatus(): int
    {
        return $this->isPermanent ? 301 : 302;
    }

    public function send(): void {
        $this->sendHeaders();
        $this->sendStatus($this->getStatus());
    }
}