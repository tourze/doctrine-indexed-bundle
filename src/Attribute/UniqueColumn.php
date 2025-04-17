<?php

namespace Tourze\DoctrineIndexedBundle\Attribute;

/**
 * 标记指定实体的成员关联一个唯一索引
 */
#[\Attribute(\Attribute::TARGET_PROPERTY)]
class UniqueColumn
{
    public function __construct(
        public readonly string $group = 'group1',
    ) {
    }
}
