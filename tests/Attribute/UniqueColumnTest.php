<?php

declare(strict_types=1);

namespace Tourze\DoctrineIndexedBundle\Tests\Attribute;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Tourze\DoctrineIndexedBundle\Attribute\UniqueColumn;

/**
 * UniqueColumn属性测试
 *
 * @internal
 */
#[CoversClass(UniqueColumn::class)]
final class UniqueColumnTest extends TestCase
{
    /**
     * 测试构造函数和属性
     */
    public function testConstructor(): void
    {
        // 测试默认值
        $uniqueColumn = new UniqueColumn();
        $this->assertSame('group1', $uniqueColumn->group);

        // 测试自定义值
        $customGroup = 'custom_group';
        $uniqueColumn = new UniqueColumn($customGroup);
        $this->assertSame($customGroup, $uniqueColumn->group);
    }
}
