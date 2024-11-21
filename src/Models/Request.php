<?php

namespace Nacho\Models;

use Exception;
use Nacho\Contracts\RequestInterface;
use Nacho\Contracts\RouteInterface;
use Nacho\Helpers\ServerVarsParser;

class Request implements RequestInterface
{
    public string $requestMethod;
    public ?string $contentType;
    public ParameterBag $headers;
    public ParameterBag $body;
    public ParameterBag $server;
    protected ?Route $route = null;

    function __construct()
    {
        $this->server = new ParameterBag($_SERVER);
        $this->bootstrapSelf();
    }

    public function setRoute(RouteInterface $route): void
    {
        $this->route = $route;
    }

    public function getRoute(): Route
    {
        if (!$this->route) {
            throw new Exception('Route has not yet been defined');
        }

        return $this->route;
    }

    private function bootstrapSelf(): void
    {
        $this->requestMethod = ServerVarsParser::getRequestMethod();
        $this->contentType = ServerVarsParser::getContentType();
        $this->headers = new ParameterBag(ServerVarsParser::parseHeaders());
        $this->body = new ParameterBag();
    }

    public function getFiles(): array
    {
        return $_FILES;
    }

    public function isMethod(string $method): bool
    {
        return strtoupper($this->requestMethod) === strtoupper($method);
    }

    public function getBody(): ParameterBag
    {
        if ($this->body->count()) {
            return $this->body;
        }
        $unsafe = [];
        if ($this->isMethod(HttpMethod::GET)) {
            $unsafe = $_GET;
        }

        if ($this->isMethod(HttpMethod::POST)) {
            $unsafe = $_POST;
        }

        if (in_array($this->requestMethod, [HttpMethod::PUT, HttpMethod::DELETE])) {
            $requestContent = file_get_contents("php://input");
            if ($this->contentType === 'application/json') {
                $unsafe = json_decode($requestContent, true);
            } else {
                parse_str($requestContent, $unsafe);
            }
        }
        unset($this->body);
        $this->body = new ParameterBag($this->filterArrayDeep($unsafe));

        return $this->body;
    }

    public function getAcceptedContentTypes(): array
    {
        // Values will be stored in this array
        $acceptTypes = [];

        // Accept header is case insensitive, and whitespace isn’t important
        $accept = strtolower(str_replace(' ', '', $this->headers->get('ACCEPT')));
        // divide it into parts in the place of a ","
        $accept = explode(',', $accept);
        foreach ($accept as $a) {
            // the default quality is 1.
            $q = 1;
            // check if there is a different quality
            if (strpos($a, ';q=')) {
                // divide "mime/type;q=X" into two parts: "mime/type" i "X"
                list($a, $q) = explode(';q=', $a);
            }
            // mime-type $a is accepted with the quality $q
            $acceptTypes[$a] = $q;
        }
        arsort($acceptTypes);

        return $acceptTypes;
    }

    private function filterArrayDeep(array $arr): array
    {
        foreach ($arr as $key => $value) {
            if (is_array($value)) {
                $arr[$key] = $this->filterArrayDeep($value);
            } else {
                $value = str_replace('<script>', '&lt;script&gt;', $value);
                $value = str_replace('</script>', '&lt;/script&gt;', $value);
                $arr[$key] = $value;
            }
        }
        return $arr;
    }
}

