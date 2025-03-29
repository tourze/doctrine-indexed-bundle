<?php

namespace Tourze\DoctrineIndexedBundle\Attribute;

/**
 * 标记指定实体的成员是一个需要索引的字段
 */
#[\Attribute(\Attribute::TARGET_PROPERTY)]
class IndexColumn
{
    public function __construct(
        public readonly ?string $name = null,
    ) {
    }
}
