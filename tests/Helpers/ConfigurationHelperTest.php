<?php

namespace Test\Helpers;

use Nacho\Exceptions\ConfigurationDoesNotExistException;
use Nacho\Helpers\ConfigurationHelper;
use PHPUnit\Framework\TestCase;

class ConfigurationHelperTest extends TestCase
{
    private array $testConf;

    public function __construct(string $name)
    {
        parent::__construct($name);
        $this->testConf = require(__DIR__ . '/../data/exampleconfig/config.php');
    }

    public function testConfigInitialization(): void
    {
        $conf = ConfigurationHelper::getInstance($this->testConf);
        $this->assertInstanceOf(ConfigurationHelper::class, $conf);
    }

    public function testGetInvalidConfig(): void
    {
        $this->expectException(ConfigurationDoesNotExistException::class);
        $conf = ConfigurationHelper::getInstance($this->testConf);
        $conf->getCustomConfig('invalid');
    }
//
//    public function testGetHooks()
//    {
//
//    }
//
//    public function testGetCustomConfig()
//    {
//
//    }
//
//    public function testGetInstance()
//    {
//
//    }
//
//    public function testGetAlternativeContentHandlers()
//    {
//
//    }
//
//    public function testGetSecurity()
//    {
//
//    }
//
//    public function testGetOrm()
//    {
//
//    }
//
//    public function testGetRoutes()
//    {
//
//    }
}
