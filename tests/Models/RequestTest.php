<?php

namespace Test;

use Nacho\Controllers\TestController;
use Nacho\Models\Request;
use Nacho\Models\Route;
use PHPUnit\Framework\TestCase;

class RequestTest extends TestCase
{
    public function testInitialization(): void
    {
        $this->setServerVariables();
        $instance = Request::getInstance();
        $this->assertInstanceOf(Request::class, $instance);
        $this->assertEquals('GET', $instance->requestMethod);
        $this->assertIsString($instance->documentRoot);
    }

    public function testGetRequest()
    {
        $this->setServerVariables();
        $_GET['test'] = 'test';
        $instance = Request::getInstance();
        $this->assertArrayHasKey('test', $instance->getBody());
    }

    public function testPostRequest(): void
    {
        $this->setServerVariables();
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST['test'] = 'test';
        $instance = Request::getInstance();
        $this->assertArrayHasKey('test', $instance->getBody());
    }

    public function testPutRequest(): void
    {
        file_put_contents('php://input', json_encode(['test' => 'test']));
        $this->setServerVariables();
        $_SERVER['REQUEST_METHOD'] = 'PUT';
        $instance = Request::getInstance();
        $this->assertArrayHasKey('test', $instance->getBody());
    }

    public function testGetRouteNotSet(): void
    {
        $this->setServerVariables();
        $instance = Request::getInstance();
        $this->expectExceptionMessage('Route has not yet been defined');
        $instance->getRoute();
    }

    public function testGetRouteValid(): void
    {
        $this->setServerVariables();
        $_SERVER['REQUEST_URI'] = '/api/init';
        $instance = Request::getInstance();
        $route = new Route([
            'route' => '/tests/index',
            'controller' => TestController::class,
            'function' => 'index',
        ]);
        $instance->setRoute($route);
        $this->assertInstanceOf(Route::class, $instance->getRoute());
    }

//    public function testGetFiles()
//    {
//
//    }

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
