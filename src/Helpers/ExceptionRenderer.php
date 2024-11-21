<?php

namespace Nacho\Helpers;

use Nacho\Contracts\Response;
use Nacho\Exceptions\NachoException;
use Nacho\Models\HttpResponse;
use Nacho\Models\Request;

class ExceptionRenderer
{
    private Request $request;
    private bool $debug;

    public function __construct(Request $request, bool $debug = false)
    {
        $this->request = $request;
        $this->debug = $debug;
    }

    public function renderException(NachoException $exception): Response
    {
        if ($this->shouldForceJsonResponse()) {
            $json = [
                'code' => $exception->getCode(),
                'error' => $exception->getMessage()
            ];
            $headers = ['content-type' => 'application/json'];
            return new HttpResponse(json_encode($json), $exception->getCode(), $headers);
        } else if ($this->shouldForceHtmlResponse()) {
            // TODO: HTML responses needs to be implemented
        }

    }

    protected function shouldForceJsonResponse(): bool
    {
        if ($this->matchAcceptedContentType("application/json")) {
            return false;
        }

        $path = $this->request->getRoute()->getPath();

        return str_starts_with('/api', $path) || str_starts_with('api', $path);

    }

    protected function shouldForceHtmlResponse(): bool
    {
        return $this->matchAcceptedContentType("text/html");
    }

    private function matchAcceptedContentType(string $contentType): bool
    {
        foreach ($this->request->getAcceptedContentTypes() as $mime => $quality) {
            if ($mime === $contentType) {
                return true;
            }
        }

        return false;
    }
}
