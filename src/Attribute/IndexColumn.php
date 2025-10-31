<?php

declare(strict_types=1);

namespace Tourze\DoctrineIndexedBundle\Attribute;

/**
 * 标记指定实体的成员是一个需要索引的字段
 */
#[\Attribute(flags: \Attribute::TARGET_PROPERTY)]
readonly class IndexColumn
{
    public function __construct(
        public ?string $name = null,
    ) {
    }
}
