<?php

namespace Tests\ORM;

use DI\ContainerBuilder;
use Nacho\Nacho;
use Nacho\ORM\AbstractRepository;
use Nacho\ORM\ModelInterface;
use Nacho\ORM\RepositoryManagerInterface;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class RepositoryTest extends TestCase
{
    private RepositoryManagerInterface $repositoryManager;

    protected function setUp(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $this->repositoryManager = $this->createMock(RepositoryManagerInterface::class);

        $builder = new ContainerBuilder();
        $builder->addDefinitions([
            LoggerInterface::class => $logger,
            RepositoryManagerInterface::class => $this->repositoryManager,
        ]);

        Nacho::$container = $builder->build();
    }

    public function testInit(): void
    {
        $this->repositoryManager->expects($this->once())
            ->method('trackRepository');
        $repository = new TestRepository();
    }

    public function testSetWithEmptyData(): void
    {
        $repository = new TestRepository();
        $dummyData = $this->generateMockData(1);
        $repository->set($dummyData);
        $this->assertCount(1, $repository->getTestData(), 'There\'s not exactly one element in the data array');
        $this->assertEquals($dummyData, $repository->getTestData()[1], 'The first data element is not equals to our dummy Object');
        $this->assertTrue($repository->isDataChanged());
    }

    public function testSetWithMultipleData(): void
    {
        $repository = new TestRepository();
        $dummy1 = $this->generateMockData(1);
        $dummy2 = $this->generateMockData(2);

        $repository->set($dummy1);
        $this->assertCount(1, $repository->getTestData());
        $repository->set($dummy2);
        $this->assertCount(2, $repository->getTestData());
    }

    public function testSetOverwriteDuplicate(): void
    {
        $repository = new TestRepository();
        $dummy1 = $this->generateMockData(1);

        $repository->set($dummy1);
        $repository->set($dummy1);

        $this->assertCount(1, $repository->getTestData());
    }

    public function testSetOverwriteSameId(): void
    {
        $repository = new TestRepository();
        $dummy1 = $this->generateMockData(1);
        $dummy2 = $this->generateMockData(1);

        $repository->set($dummy1);
        $repository->set($dummy2);

        $this->assertCount(1, $repository->getTestData());
        $this->assertEquals($dummy2, $repository->getTestData()[1]);
    }

    private function generateMockData(int $id): ModelInterface
    {
        $mockData = $this->createMock(ModelInterface::class);
        $mockData->method('getId')->willReturn($id);
        $mockData->method('toArray')->willReturn(['test' => 'data_' . $id]);
        return $mockData;
    }
}

class TestRepository extends AbstractRepository
{
    public static function getDataName(): string
    {
        return 'test_repo';
    }

    public function getTestData(): array
    {
        return $this->getData();
    }
}