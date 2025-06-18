<?php

namespace Tourze\DoctrineIndexedBundle\Tests\EventSubscriber;

use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\ClassMetadata;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionProperty;
use Tourze\DoctrineIndexedBundle\Attribute\FulltextColumn;
use Tourze\DoctrineIndexedBundle\Attribute\IndexColumn;
use Tourze\DoctrineIndexedBundle\EventSubscriber\AddIndexedListener;
use Yiisoft\Strings\Inflector;

/**
 * AddIndexedListener测试类
 */
class AddIndexedListenerTest extends TestCase
{
    private AddIndexedListener $listener;
    private LoadClassMetadataEventArgs $eventArgs;
    private ClassMetadata $classMetadata;

    protected function setUp(): void
    {
        $this->listener = new AddIndexedListener(new Inflector());
        
        // 模拟ClassMetadata
        $this->classMetadata = $this->createMock(ClassMetadata::class);
        $this->classMetadata->table = ['name' => 'test_table'];
        
        // 模拟LoadClassMetadataEventArgs
        $this->eventArgs = $this->createMock(LoadClassMetadataEventArgs::class);
        $this->eventArgs->method('getClassMetadata')
            ->willReturn($this->classMetadata);
    }

    /**
     * 测试普通索引的生成
     */
    public function testAddIndexColumn(): void
    {
        // 创建一个带有IndexColumn属性的属性反射
        $property = new ReflectionProperty(TestEntity::class, 'indexedField');
        
        // 模拟ReflectionClass
        $reflectionClass = $this->createMock(ReflectionClass::class);
        $reflectionClass->method('getProperties')
            ->willReturn([$property]);
        
        $this->classMetadata->method('getReflectionClass')
            ->willReturn($reflectionClass);
        
        // 执行测试
        $this->listener->loadClassMetadata($this->eventArgs);
        
        // 验证索引是否正确添加
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
        $property = new ReflectionProperty(TestEntity::class, 'fulltextField');
        
        // 模拟ReflectionClass
        $reflectionClass = $this->createMock(ReflectionClass::class);
        $reflectionClass->method('getProperties')
            ->willReturn([$property]);
        
        $this->classMetadata->method('getReflectionClass')
            ->willReturn($reflectionClass);
        
        // 执行测试
        $this->listener->loadClassMetadata($this->eventArgs);
        
        // 验证索引是否正确添加
        $this->assertArrayHasKey('test_table_fulltext_fulltext_field', $this->classMetadata->table['indexes']);
        $this->assertEquals(
            [
                'columns' => ['fulltext_field'],
                'flags' => ['fulltext']
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
        $property = new ReflectionProperty(TestEntity::class, 'customIndexField');
        
        // 模拟ReflectionClass
        $reflectionClass = $this->createMock(ReflectionClass::class);
        $reflectionClass->method('getProperties')
            ->willReturn([$property]);
        
        $this->classMetadata->method('getReflectionClass')
            ->willReturn($reflectionClass);
        
        // 执行测试
        $this->listener->loadClassMetadata($this->eventArgs);
        
        // 验证索引是否正确添加
        $this->assertArrayHasKey('custom_index_name', $this->classMetadata->table['indexes']);
    }

    /**
     * 测试长表名的索引名生成
     */
    public function testLongTableNameIndexGeneration(): void
    {
        $this->classMetadata->table['name'] = str_repeat('very_long_table_name_', 10);
        
        // 创建一个带有IndexColumn属性的属性反射
        $property = new ReflectionProperty(TestEntity::class, 'indexedField');
        
        // 模拟ReflectionClass
        $reflectionClass = $this->createMock(ReflectionClass::class);
        $reflectionClass->method('getProperties')
            ->willReturn([$property]);
        
        $this->classMetadata->method('getReflectionClass')
            ->willReturn($reflectionClass);
        
        // 执行测试
        $this->listener->loadClassMetadata($this->eventArgs);
        
        // 验证生成的索引名是否符合长度限制
        foreach ($this->classMetadata->table['indexes'] as $indexName => $index) {
            $this->assertLessThanOrEqual(64, strlen($indexName));
        }
    }
}

/**
 * 用于测试的实体类
 */
class TestEntity
{
    #[ORM\Column]
    #[IndexColumn]
    private string $indexedField;

    #[ORM\Column]
    #[FulltextColumn]
    private string $fulltextField;

    #[ORM\Column]
    #[IndexColumn(name: 'custom_index_name')]
    private string $customIndexField;

    public function getIndexedField(): string
    {
        return $this->indexedField;
    }

    public function getFulltextField(): string
    {
        return $this->fulltextField;
    }

    public function getCustomIndexField(): string
    {
        return $this->customIndexField;
    }

    public function setIndexedField(string $indexedField): self
    {
        $this->indexedField = $indexedField;
        return $this;
    }

    public function setFulltextField(string $fulltextField): self
    {
        $this->fulltextField = $fulltextField;
        return $this;
    }

    public function setCustomIndexField(string $customIndexField): self
    {
        $this->customIndexField = $customIndexField;
        return $this;
    }
}
