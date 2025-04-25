<?php

namespace Tourze\DoctrineIndexedBundle\Tests\Attribute;

use PHPUnit\Framework\TestCase;
use Tourze\DoctrineIndexedBundle\Attribute\IndexColumn;

/**
 * IndexColumn属性测试
 */
class IndexColumnTest extends TestCase
{
    /**
     * 测试构造函数和属性
     */
    public function testConstructor(): void
    {
        // 测试默认值
        $indexColumn = new IndexColumn();
        $this->assertNull($indexColumn->name);

        // 测试自定义值
        $customName = 'custom_index_name';
        $indexColumn = new IndexColumn($customName);
        $this->assertSame($customName, $indexColumn->name);
    }
}
