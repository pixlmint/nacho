<?php

namespace Nacho\Models;

use Nacho\Contracts\RequestInterface;
use Nacho\Helpers\ServerVarsParser;

class Request implements RequestInterface
{
    public string $requestMethod;
    public string $contentType;
    public array $headers;
    protected Route $route;

    function __construct(Route $route)
    {
        $this->bootstrapSelf();
        $this->route = $route;
    }

    private function bootstrapSelf()
    {
        foreach ($_SERVER as $key => $value) {
            $this->{$this->toCamelCase($key)} = $value;
        }
        $this->requestMethod = ServerVarsParser::getRequestMethod();
        $this->contentType = ServerVarsParser::getContentType();
        $this->headers = ServerVarsParser::parseHeaders();
    }

    private function toCamelCase($string)
    {
        $result = strtolower($string);

        preg_match_all('/_[a-z]/', $result, $matches);

        foreach ($matches[0] as $match) {
            $c = str_replace('_', '', strtoupper($match));
            $result = str_replace($match, $c, $result);
        }

        return $result;
    }

    public function getBody(): array
    {
        $unsafe = [];
        if ($this->requestMethod === HttpMethod::GET) {
            return [];
        }

        if ($this->requestMethod === HttpMethod::POST) {
            $unsafe = $_POST;
        }

        if (in_array($this->requestMethod, [HttpMethod::PUT, HttpMethod::DELETE])) {
            $requestContent = file_get_contents("php://input");
            parse_str($requestContent, $unsafe);
        }

        return $this->filterArrayDeep($unsafe);
    }

    public function getRoute(): Route
    {
        return $this->route;
    }

    private function filterArrayDeep(array $arr): array
    {
        foreach ($arr as $key => $value) {
            if (is_array($value)) {
                $arr[$key] = $this->filterArrayDeep($value);
            } else {
                $arr[$key] = filter_var($value, FILTER_SANITIZE_STRING);
                $arr[$key] = str_replace($arr[$key], '&#34;', "'");
            }
        }
        return $arr;
    }
}
