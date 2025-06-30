<?php

namespace Tourze\DoctrineIndexedBundle\EventSubscriber;

use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\Events;
use Doctrine\ORM\Mapping as ORM;
use Tourze\DoctrineIndexedBundle\Attribute\FulltextColumn;
use Tourze\DoctrineIndexedBundle\Attribute\IndexColumn;
use Yiisoft\Strings\Inflector;

/**
 * 自动补充索引
 *
 * @see https://alexkunin.medium.com/doctrine-symfony-adding-indexes-to-fields-defined-in-traits-a8e480af66b2
 */
#[AsDoctrineListener(event: Events::loadClassMetadata)]
class AddIndexedListener
{
    public function __construct(
        private readonly Inflector $inflector,
    ) {
    }

    private function getIndexName(string $tableName, string $columnName, string $type): string
    {
        $idxMaxLength = 64;

        // 索引统一使用 表名_idx_字段名，之所以这样是因为某些dbms索引名要求全局唯一
        $idxName = "{$tableName}_{$type}_{$columnName}";
        // 有一种情况，那就是表名太长时，字段不一定够用，此时我们需要缩短一些
        // @see https://blog.csdn.net/vkingnew/article/details/83898542
        if (strlen($idxName) > $idxMaxLength) {
            $idxName = md5($tableName) . "_{$type}_{$columnName}";
        }
        // 如果还是太长的话，那就只能降级了
        if (strlen($idxName) > $idxMaxLength) {
            $idxName = substr(md5($tableName), 0, 16) . "_{$type}_{$columnName}";
        }
        // 终极方案了
        if (strlen($idxName) > $idxMaxLength) {
            $idxName = md5("{$tableName}_{$type}_{$columnName}");
        }

        return $idxName;
    }

    public function loadClassMetadata(LoadClassMetadataEventArgs $eventArgs): void
    {
        $cm = $eventArgs->getClassMetadata();

        foreach ($cm->getReflectionClass()->getProperties() as $property) {
            $ormColumn = $property->getAttributes(ORM\Column::class);
            if (empty($ormColumn)) {
                continue;
            }
            $ormColumn = $ormColumn[0]->newInstance();
            /** @var ORM\Column $ormColumn */
            $name = $ormColumn->name;
            if ($name === null) {
                $name = $property->getName();
                $name = $this->inflector->toSnakeCase($name);
            }
            if (empty($name)) {
                continue;
            }

            // 索引字段
            $indexColumn = $property->getAttributes(IndexColumn::class);
            // 要注意，如果字段已经是唯一索引，那么就不需要再加索引
            if (!empty($indexColumn) && !$ormColumn->unique) {
                $indexColumn = $indexColumn[0]->newInstance();
                /* @var IndexColumn $indexColumn */
                $idxName = $indexColumn->name !== null ? $indexColumn->name : $this->getIndexName($cm->table['name'], $name, 'idx');
                $cm->table['indexes'][$idxName] = [
                    'columns' => [
                        $name,
                    ],
                ];
            }

            // 全文索引
            $fulltextColumn = $property->getAttributes(FulltextColumn::class);
            if (!empty($fulltextColumn)) {
                $fulltextColumn = $fulltextColumn[0]->newInstance();
                /* @var FulltextColumn $fulltextColumn */
                $idxName = $fulltextColumn->name !== null ? $fulltextColumn->name : $this->getIndexName($cm->table['name'], $name, 'fulltext');
                $cm->table['indexes'][$idxName] = [
                    'columns' => [
                        $name,
                    ],
                    'flags' => ['fulltext'],
                ];
            }
        }
    }
}
