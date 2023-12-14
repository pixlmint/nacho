<?php

namespace Tests\Models;

use Nacho\Controllers\TestController;
use Nacho\Models\Request;
use PHPUnit\Framework\TestCase;

class RequestTest extends TestCase
{
    private Request $request;

    protected function setUp(): void
    {
        $this->setServerVariables();
        $this->request = new Request();
    }

    public function testInitialization(): void
    {
        $this->assertInstanceOf(Request::class, $this->request);
        $this->assertEquals('GET', $this->request->requestMethod);
        $this->assertIsString($this->request->documentRoot);
    }

    public function testGetRequest()
    {
        $_GET['test'] = 'test';
        $this->assertArrayHasKey('test', $this->request->getBody());
    }

    public function testPostRequest(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST['test'] = 'test';
        $this->assertArrayHasKey('test', $this->request->getBody());
    }

    public function testPutRequest(): void
    {
        file_put_contents('php://input', json_encode(['test' => 'test']));
        $_SERVER['REQUEST_METHOD'] = 'PUT';
        $this->assertArrayHasKey('test', $this->request->getBody());
    }

    public function testGetRouteNotSet(): void
    {
        $this->expectExceptionMessage('Route has not yet been defined');
        $this->request->getRoute();
    }

    /**
     * Sets some basic $_SERVER variables wihch were taken from a real request
     */
    private function setServerVariables(): void
    {
        $_SERVER["REDIRECT_STATUS"] = "200";
        $_SERVER["HTTP_USER_AGENT"] = "PostmanRuntime/7.29.3";
        $_SERVER["HTTP_ACCEPT"] = "*/*";
        $_SERVER["HTTP_CACHE_CONTROL"] = "no-cache";
        $_SERVER["HTTP_POSTMAN_TOKEN"] = "40a94ca2-d5ae-42b1-a973-f798d958caaa";
        $_SERVER["HTTP_HOST"] = "127.0.0.1:94";
        $_SERVER["HTTP_ACCEPT_ENCODING"] = "gzip, deflate, br";
        $_SERVER["HTTP_CONNECTION"] = "keep-alive";
        $_SERVER["PATH"] = "/usr/local/sbin:/usr/local/bin:/usr/sbin:/usr/bin:/sbin:/bin";
        $_SERVER["SERVER_SIGNATURE"] = "<address>Apache/2.4.57 (Debian) Server at 127.0.0.1 Port 94</address>";
        $_SERVER["SERVER_SOFTWARE"] = "Apache/2.4.57 (Debian)";
        $_SERVER["SERVER_NAME"] = "127.0.0.1";
        $_SERVER["SERVER_ADDR"] = "172.27.0.2";
        $_SERVER["SERVER_PORT"] = "94";
        $_SERVER["REMOTE_ADDR"] = "172.27.0.1";
        $_SERVER["DOCUMENT_ROOT"] = "/var/www/html";
        $_SERVER["REQUEST_SCHEME"] = "http";
        $_SERVER["CONTEXT_PREFIX"] = "";
        $_SERVER["CONTEXT_DOCUMENT_ROOT"] = "/var/www/html";
        $_SERVER["SERVER_ADMIN"] = "webmaster@localhost";
        $_SERVER["SCRIPT_FILENAME"] = "/var/www/html/index.php";
        $_SERVER["REMOTE_PORT"] = "57490";
        $_SERVER["REDIRECT_URL"] = "/api/init";
        $_SERVER["GATEWAY_INTERFACE"] = "CGI/1.1";
        $_SERVER["SERVER_PROTOCOL"] = "HTTP/1.1";
        $_SERVER["REQUEST_METHOD"] = "GET";
        $_SERVER["QUERY_STRING"] = "";
        $_SERVER["REQUEST_URI"] = "/api/init";
        $_SERVER["SCRIPT_NAME"] = "/index.php";
        $_SERVER["PHP_SELF"] = "/index.php";
        $_SERVER["REQUEST_TIME_FLOAT"] = "1695921309.3965";
        $_SERVER["REQUEST_TIME"] = "1695921309";
    }
}
