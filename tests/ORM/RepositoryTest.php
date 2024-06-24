<?php

namespace Tests\ORM;

use DI\ContainerBuilder;
use Nacho\Nacho;
use Nacho\ORM\AbstractRepository;
use Nacho\ORM\ModelInterface;
use Nacho\ORM\RepositoryManagerInterface;
use Nacho\ORM\TemporaryModel;
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
        $dummyData = new TestData(1);
        $repository->set($dummyData);
        $this->assertCount(1, $repository->getTestData(), 'There\'s not exactly one element in the data array');
        $this->assertEquals($dummyData, $repository->getTestData()[1], 'The first data element is not equals to our dummy Object');
        $this->assertTrue($repository->isDataChanged());
    }

    public function testSetWithMultipleData(): void
    {
        $repository = new TestRepository();
        $dummy1 = new TestData(1);
        $dummy2 = new TestData(2);

        $repository->set($dummy1);
        $this->assertCount(1, $repository->getTestData());
        $repository->set($dummy2);
        $this->assertCount(2, $repository->getTestData());
    }

    public function testSetOverwriteDuplicate(): void
    {
        $repository = new TestRepository();
        $dummy1 = new TestData(1);

        $repository->set($dummy1);
        $repository->set($dummy1);

        $this->assertCount(1, $repository->getTestData());
    }

    public function testSetOverwriteSameId(): void
    {
        $repository = new TestRepository();
        $dummy1 = new TestData(1);
        $dummy2 = new TestData(1);

        $repository->set($dummy1);
        $repository->set($dummy2);

        $this->assertCount(2, $repository->getTestData());
        $this->assertEquals($dummy2, $repository->getTestData()[1]);
    }

    public function testSetWithDefaultId(): void
    {
        $repository = new TestRepository();
        $dummy = new TestData(-1);
        $dummy2 = new TestData(-1);

        $repository->set($dummy);
        $repository->set($dummy2);

        $this->assertCount(2, $repository->getTestData());
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

    protected static function getModel(): string
    {
        return TestData::class;
    }
}

class TestData implements ModelInterface
{
    private int $id;
    public function __construct(int $id)
    {
        $this->id = $id;
    }

    public static function init(TemporaryModel $data, int $id): ModelInterface
    {
        return new TestData($id);
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function toArray(): array
    {
        return [
            'testdata' => 'id_' . $this->id,
        ];
    }
}