<?php

namespace Tests\Models;

use DI\ContainerBuilder;
use Nacho\Models\ParameterBag;
use Nacho\Nacho;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class ParameterBagTest extends TestCase
{
    public function testDeprecatedLogging(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $builder = new ContainerBuilder();
        $builder->addDefinitions([
            LoggerInterface::class => $logger,
        ]);

        Nacho::$container = $builder->build();

        $logger->expects($this->atLeastOnce())
            ->method('warning')
            ->with($this->logicalAnd($this->stringContains('Using array access on ParameterBag class. Backtrace'), $this->stringContains('ParameterBagTest.php')));

        $bag = new ParameterBag();
        $bag->set('testval', 1);
        $this->assertEquals(1, $bag['testval']);
    }
}