<?php

namespace Tourze\DoctrineIndexedBundle\Tests\EventSubscriber;

use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\Mapping\ClassMetadata;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionProperty;
use Tourze\DoctrineIndexedBundle\Attribute\FulltextColumn;
use Tourze\DoctrineIndexedBundle\Attribute\IndexColumn;
use Tourze\DoctrineIndexedBundle\EventSubscriber\AddIndexedListener;
use Yiisoft\Strings\Inflector;

/**
 * AddIndexedListener事件订阅者测试
 */
class AddIndexedListenerTest extends TestCase
{
    private Inflector $inflector;
    private AddIndexedListener $listener;

    protected function setUp(): void
    {
        // 创建实际的Inflector实例
        $this->inflector = new Inflector();
        $this->listener = new AddIndexedListener($this->inflector);
    }

    /**
     * 测试getIndexName方法
     */
    public function testGetIndexName(): void
    {
        $reflectionClass = new ReflectionClass(AddIndexedListener::class);
        $method = $reflectionClass->getMethod('getIndexName');
        $method->setAccessible(true);

        // 测试正常长度的索引名
        $result = $method->invoke($this->listener, 'users', 'email', 'idx');
        $this->assertEquals('users_idx_email', $result);

        // 测试超长的表名导致的索引名
        $longTableName = str_repeat('very_long_table_name_', 10); // 生成一个很长的表名
        $result = $method->invoke($this->listener, $longTableName, 'email', 'idx');
        $this->assertStringContainsString('_idx_email', $result);
        $this->assertLessThanOrEqual(64, strlen($result));
    }

    /**
     * 测试loadClassMetadata方法处理普通索引
     */
    public function testLoadClassMetadataWithIndexColumn(): void
    {
        // 创建模拟对象
        /** @var ClassMetadata&MockObject $classMetadata */
        $classMetadata = $this->createMock(ClassMetadata::class);
        $classMetadata->table = ['name' => 'test_table'];

        // 创建一个模拟的带有IndexColumn属性的属性
        $property = $this->createIndexColumnPropertyMock();

        // 创建反射类
        $reflectionClass = $this->createMock(ReflectionClass::class);
        $reflectionClass->method('getProperties')->willReturn([$property]);

        // 设置ClassMetadata
        $classMetadata->method('getReflectionClass')->willReturn($reflectionClass);

        // 创建LoadClassMetadataEventArgs
        /** @var LoadClassMetadataEventArgs&MockObject $eventArgs */
        $eventArgs = $this->createMock(LoadClassMetadataEventArgs::class);
        $eventArgs->method('getClassMetadata')->willReturn($classMetadata);

        // 调用测试方法
        $this->listener->loadClassMetadata($eventArgs);

        // 验证索引是否被正确添加
        $this->assertArrayHasKey('test_table_idx_test_column', $classMetadata->table['indexes']);
        $this->assertEquals(['test_column'], $classMetadata->table['indexes']['test_table_idx_test_column']['columns']);
    }

    /**
     * 测试loadClassMetadata方法处理全文索引
     */
    public function testLoadClassMetadataWithFulltextColumn(): void
    {
        // 创建模拟对象
        /** @var ClassMetadata&MockObject $classMetadata */
        $classMetadata = $this->createMock(ClassMetadata::class);
        $classMetadata->table = ['name' => 'test_table'];

        // 创建一个模拟的带有FulltextColumn属性的属性
        $property = $this->createFulltextColumnPropertyMock();

        // 创建反射类
        $reflectionClass = $this->createMock(ReflectionClass::class);
        $reflectionClass->method('getProperties')->willReturn([$property]);

        // 设置ClassMetadata
        $classMetadata->method('getReflectionClass')->willReturn($reflectionClass);

        // 创建LoadClassMetadataEventArgs
        /** @var LoadClassMetadataEventArgs&MockObject $eventArgs */
        $eventArgs = $this->createMock(LoadClassMetadataEventArgs::class);
        $eventArgs->method('getClassMetadata')->willReturn($classMetadata);

        // 调用测试方法
        $this->listener->loadClassMetadata($eventArgs);

        // 验证索引是否被正确添加
        $this->assertArrayHasKey('test_table_fulltext_test_column', $classMetadata->table['indexes']);
        $this->assertEquals(['test_column'], $classMetadata->table['indexes']['test_table_fulltext_test_column']['columns']);
        $this->assertEquals(['fulltext'], $classMetadata->table['indexes']['test_table_fulltext_test_column']['flags']);
    }

    /**
     * 创建带有IndexColumn属性的属性模拟对象
     *
     * @return ReflectionProperty&MockObject
     */
    private function createIndexColumnPropertyMock(): mixed
    {
        /** @var ReflectionProperty&MockObject $property */
        $property = $this->createMock(ReflectionProperty::class);
        $property->method('getName')->willReturn('testColumn');

        // 模拟ORM\Column属性
        $ormColumnAttribute = new class {
            public function newInstance(): object
            {
                return new class {
                    public $name = 'test_column';
                    public $unique = false;
                };
            }
        };
        $property->method('getAttributes')->willReturnCallback(function ($attributeClass) use ($ormColumnAttribute) {
            if ($attributeClass === \Doctrine\ORM\Mapping\Column::class) {
                return [$ormColumnAttribute];
            }

            if ($attributeClass === IndexColumn::class) {
                $indexColumnAttribute = new class {
                    public function newInstance(): IndexColumn
                    {
                        return new IndexColumn();
                    }
                };
                return [$indexColumnAttribute];
            }

            return [];
        });

        return $property;
    }

    /**
     * 创建带有FulltextColumn属性的属性模拟对象
     *
     * @return ReflectionProperty&MockObject
     */
    private function createFulltextColumnPropertyMock(): mixed
    {
        /** @var ReflectionProperty&MockObject $property */
        $property = $this->createMock(ReflectionProperty::class);
        $property->method('getName')->willReturn('testColumn');

        // 模拟ORM\Column属性
        $ormColumnAttribute = new class {
            public function newInstance(): object
            {
                return new class {
                    public $name = 'test_column';
                    public $unique = false;
                };
            }
        };
        $property->method('getAttributes')->willReturnCallback(function ($attributeClass) use ($ormColumnAttribute) {
            if ($attributeClass === \Doctrine\ORM\Mapping\Column::class) {
                return [$ormColumnAttribute];
            }

            if ($attributeClass === FulltextColumn::class) {
                $fulltextColumnAttribute = new class {
                    public function newInstance(): FulltextColumn
                    {
                        return new FulltextColumn();
                    }
                };
                return [$fulltextColumnAttribute];
            }

            return [];
        });

        return $property;
    }
}
