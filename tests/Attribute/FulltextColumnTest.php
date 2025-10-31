<?php

declare(strict_types=1);

namespace Tourze\DoctrineIndexedBundle\Tests\Attribute;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Tourze\DoctrineIndexedBundle\Attribute\FulltextColumn;

/**
 * FulltextColumn属性测试
 *
 * @internal
 */
#[CoversClass(FulltextColumn::class)]
final class FulltextColumnTest extends TestCase
{
    /**
     * 测试构造函数和属性
     */
    public function testConstructor(): void
    {
        // 测试默认值
        $fulltextColumn = new FulltextColumn();
        $this->assertNull($fulltextColumn->name);

        // 测试自定义值
        $customName = 'custom_fulltext_name';
        $fulltextColumn = new FulltextColumn($customName);
        $this->assertSame($customName, $fulltextColumn->name);
    }
}
