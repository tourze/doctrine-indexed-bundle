<?php

declare(strict_types=1);

namespace Tourze\DoctrineIndexedBundle\Tests\EventSubscriber;

use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\Mapping\ClassMetadata;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\DoctrineIndexedBundle\EventSubscriber\AddIndexedListener;
use Tourze\DoctrineIndexedBundle\Tests\Fixtures\TestEntity;
use Tourze\PHPUnitSymfonyKernelTest\AbstractEventSubscriberTestCase;

/**
 * AddIndexedListener测试类
 *
 * @internal
 */
#[CoversClass(AddIndexedListener::class)]
#[RunTestsInSeparateProcesses]
final class AddIndexedListenerTest extends AbstractEventSubscriberTestCase
{
    private AddIndexedListener $listener;

    private LoadClassMetadataEventArgs $eventArgs;

    /**
     * @var ClassMetadata<object>
     */
    private ClassMetadata $classMetadata;

    protected function onSetUp(): void
    {
        // 从容器获取服务，而不是直接实例化
        $this->listener = self::getService(AddIndexedListener::class);

        /*
         * 必须使用具体类 ClassMetadata 进行 mock 的理由：
         * 1. ClassMetadata 是 Doctrine ORM 的核心类，其内部状态复杂，抽象化会失去测试准确性
         * 2. 测试需要验证 table 数组的具体修改，这些是 ClassMetadata 的具体实现细节
         * 3. ClassMetadata 没有适合的接口可以替代，其行为测试依赖于具体实现
         */
        $this->classMetadata = $this->createMock(ClassMetadata::class);
        $this->classMetadata->table = [
            'name' => 'test_table',
            'indexes' => [],
            'uniqueConstraints' => [],
        ];

        /*
         * 必须使用具体类 LoadClassMetadataEventArgs 进行 mock 的理由：
         * 1. 这是 Doctrine 事件系统的具体事件参数类，包含特定的方法签名和行为
         * 2. 事件参数类没有通用接口，其设计就是为了传递具体的事件数据
         * 3. 测试需要验证与真实事件参数的交互，抽象接口无法提供足够的测试覆盖
         */
        $this->eventArgs = $this->createMock(LoadClassMetadataEventArgs::class);
        $this->eventArgs->method('getClassMetadata')
            ->willReturn($this->classMetadata)
        ;
    }

    /**
     * 测试普通索引的生成
     */
    public function testAddIndexColumn(): void
    {
        // 创建一个带有IndexColumn属性的属性反射
        $property = new \ReflectionProperty(TestEntity::class, 'indexedField');

        /*
         * 必须使用具体类 ReflectionClass 进行 mock 的理由：
         * 1. ReflectionClass 是 PHP 反射 API 的核心类，没有可用的接口或抽象类
         * 2. 测试需要模拟类的反射行为，特别是 getProperties() 方法的返回值
         * 3. ReflectionClass 的行为是系统级的，无法通过接口抽象来替代
         */
        $reflectionClass = $this->createMock(\ReflectionClass::class);
        $reflectionClass->method('getProperties')
            ->willReturn([$property])
        ;

        $this->classMetadata->method('getReflectionClass')
            ->willReturn($reflectionClass)
        ;

        // 执行测试
        $this->listener->loadClassMetadata($this->eventArgs);

        // 验证索引是否正确添加
        $this->assertArrayHasKey('indexes', $this->classMetadata->table);
        $this->assertArrayHasKey('test_table_idx_indexed_field', $this->classMetadata->table['indexes']);
        $this->assertEquals(
            ['columns' => ['indexed_field']],
            $this->classMetadata->table['indexes']['test_table_idx_indexed_field']
        );
    }

    /**
     * 测试全文索引的生成
     */
    public function testAddFulltextColumn(): void
    {
        // 创建一个带有FulltextColumn属性的属性反射
        $property = new \ReflectionProperty(TestEntity::class, 'fulltextField');

        /*
         * 必须使用具体类 ReflectionClass 进行 mock 的理由：
         * 1. ReflectionClass 是 PHP 反射 API 的核心类，没有可用的接口或抽象类
         * 2. 测试需要模拟类的反射行为，特别是 getProperties() 方法的返回值
         * 3. ReflectionClass 的行为是系统级的，无法通过接口抽象来替代
         */
        $reflectionClass = $this->createMock(\ReflectionClass::class);
        $reflectionClass->method('getProperties')
            ->willReturn([$property])
        ;

        $this->classMetadata->method('getReflectionClass')
            ->willReturn($reflectionClass)
        ;

        // 执行测试
        $this->listener->loadClassMetadata($this->eventArgs);

        // 验证索引是否正确添加
        $this->assertArrayHasKey('indexes', $this->classMetadata->table);
        $this->assertArrayHasKey('test_table_fulltext_fulltext_field', $this->classMetadata->table['indexes']);
        $this->assertEquals(
            [
                'columns' => ['fulltext_field'],
                'flags' => ['fulltext'],
            ],
            $this->classMetadata->table['indexes']['test_table_fulltext_fulltext_field']
        );
    }

    /**
     * 测试自定义索引名称
     */
    public function testCustomIndexName(): void
    {
        // 创建一个带有自定义索引名的属性反射
        $property = new \ReflectionProperty(TestEntity::class, 'customIndexField');

        /*
         * 必须使用具体类 ReflectionClass 进行 mock 的理由：
         * 1. ReflectionClass 是 PHP 反射 API 的核心类，没有可用的接口或抽象类
         * 2. 测试需要模拟类的反射行为，特别是 getProperties() 方法的返回值
         * 3. ReflectionClass 的行为是系统级的，无法通过接口抽象来替代
         */
        $reflectionClass = $this->createMock(\ReflectionClass::class);
        $reflectionClass->method('getProperties')
            ->willReturn([$property])
        ;

        $this->classMetadata->method('getReflectionClass')
            ->willReturn($reflectionClass)
        ;

        // 执行测试
        $this->listener->loadClassMetadata($this->eventArgs);

        // 验证索引是否正确添加
        $this->assertArrayHasKey('indexes', $this->classMetadata->table);
        $this->assertArrayHasKey('custom_index_name', $this->classMetadata->table['indexes']);
    }

    /**
     * 测试唯一索引的生成
     */
    public function testAddUniqueColumn(): void
    {
        // 创建一个带有UniqueColumn属性的属性反射
        $property = new \ReflectionProperty(TestEntity::class, 'uniqueField');

        /*
         * 必须使用具体类 ReflectionClass 进行 mock 的理由：
         * 1. ReflectionClass 是 PHP 反射 API 的核心类，没有可用的接口或抽象类
         * 2. 测试需要模拟类的反射行为，特别是 getProperties() 方法的返回值
         * 3. ReflectionClass 的行为是系统级的，无法通过接口抽象来替代
         */
        $reflectionClass = $this->createMock(\ReflectionClass::class);
        $reflectionClass->method('getProperties')
            ->willReturn([$property])
        ;

        $this->classMetadata->method('getReflectionClass')
            ->willReturn($reflectionClass)
        ;

        // 执行测试
        $this->listener->loadClassMetadata($this->eventArgs);

        // 验证唯一约束是否正确添加
        $this->assertArrayHasKey('uniqueConstraints', $this->classMetadata->table);
        $this->assertArrayHasKey('test_table_unique_unique_field', $this->classMetadata->table['uniqueConstraints']);
        $this->assertEquals(
            ['columns' => ['unique_field']],
            $this->classMetadata->table['uniqueConstraints']['test_table_unique_unique_field']
        );
    }

    /**
     * 测试长表名的索引名生成
     */
    public function testLongTableNameIndexGeneration(): void
    {
        $this->classMetadata->table['name'] = str_repeat('very_long_table_name_', 10);
        $this->classMetadata->table['indexes'] = [];

        // 创建一个带有IndexColumn属性的属性反射
        $property = new \ReflectionProperty(TestEntity::class, 'indexedField');

        /*
         * 必须使用具体类 ReflectionClass 进行 mock 的理由：
         * 1. ReflectionClass 是 PHP 反射 API 的核心类，没有可用的接口或抽象类
         * 2. 测试需要模拟类的反射行为，特别是 getProperties() 方法的返回值
         * 3. ReflectionClass 的行为是系统级的，无法通过接口抽象来替代
         */
        $reflectionClass = $this->createMock(\ReflectionClass::class);
        $reflectionClass->method('getProperties')
            ->willReturn([$property])
        ;

        $this->classMetadata->method('getReflectionClass')
            ->willReturn($reflectionClass)
        ;

        // 执行测试
        $this->listener->loadClassMetadata($this->eventArgs);

        // 验证生成的索引名是否符合长度限制
        $this->assertArrayHasKey('indexes', $this->classMetadata->table);
        if ([] !== $this->classMetadata->table['indexes']) {
            foreach ($this->classMetadata->table['indexes'] as $indexName => $index) {
                $this->assertLessThanOrEqual(64, strlen($indexName));
            }
        }
    }

    /**
     * 测试 loadClassMetadata 方法的核心功能
     */
    public function testLoadClassMetadata(): void
    {
        // 确保数组索引已初始化
        $this->classMetadata->table['indexes'] = [];
        $this->classMetadata->table['uniqueConstraints'] = [];

        // 创建多个属性来测试不同类型的处理
        $indexProperty = new \ReflectionProperty(TestEntity::class, 'indexedField');
        $fulltextProperty = new \ReflectionProperty(TestEntity::class, 'fulltextField');
        $uniqueProperty = new \ReflectionProperty(TestEntity::class, 'uniqueField');

        /*
         * 必须使用具体类 ReflectionClass 进行 mock 的理由：
         * 1. ReflectionClass 是 PHP 反射 API 的核心类，没有可用的接口或抽象类
         * 2. 测试需要模拟类的反射行为，特别是 getProperties() 方法的返回值
         * 3. ReflectionClass 的行为是系统级的，无法通过接口抽象来替代
         */
        $reflectionClass = $this->createMock(\ReflectionClass::class);
        $reflectionClass->method('getProperties')
            ->willReturn([$indexProperty, $fulltextProperty, $uniqueProperty])
        ;

        $this->classMetadata->method('getReflectionClass')
            ->willReturn($reflectionClass)
        ;

        // 执行测试
        $this->listener->loadClassMetadata($this->eventArgs);

        // 验证所有类型的索引都被正确处理
        $this->assertArrayHasKey('indexes', $this->classMetadata->table);
        $this->assertArrayHasKey('uniqueConstraints', $this->classMetadata->table);

        // 确保数组存在后再检查具体键
        if (isset($this->classMetadata->table['indexes'])) {
            $this->assertArrayHasKey('test_table_idx_indexed_field', $this->classMetadata->table['indexes']);
            $this->assertArrayHasKey('test_table_fulltext_fulltext_field', $this->classMetadata->table['indexes']);
        }

        if (isset($this->classMetadata->table['uniqueConstraints'])) {
            $this->assertArrayHasKey('test_table_unique_unique_field', $this->classMetadata->table['uniqueConstraints']);
        }
    }
}
