<?php

namespace Tourze\DoctrineIndexedBundle\Attribute;

/**
 * 标记指定实体的成员是一个需要全文索引的字段
 */
#[\Attribute(\Attribute::TARGET_PROPERTY)]
class FulltextColumn
{
    public function __construct(
        public readonly ?string $name = null,
    ) {
    }
}
