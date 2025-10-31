<?php

declare(strict_types=1);

namespace Tourze\DoctrineIndexedBundle\EventSubscriber;

use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\Events;
use Doctrine\ORM\Mapping as ORM;
use Tourze\DoctrineIndexedBundle\Attribute\FulltextColumn;
use Tourze\DoctrineIndexedBundle\Attribute\IndexColumn;
use Tourze\DoctrineIndexedBundle\Attribute\UniqueColumn;
use Yiisoft\Strings\Inflector;

/**
 * 自动补充索引
 *
 * @see https://alexkunin.medium.com/doctrine-symfony-adding-indexes-to-fields-defined-in-traits-a8e480af66b2
 */
#[AsDoctrineListener(event: Events::loadClassMetadata)]
readonly class AddIndexedListener
{
    public function __construct(
        private Inflector $inflector,
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
            $this->processProperty($property, $cm);
        }
    }

    /**
     * @param ORM\ClassMetadata<object> $cm
     */
    private function processProperty(\ReflectionProperty $property, ORM\ClassMetadata $cm): void
    {
        $ormColumn = $this->getOrmColumn($property);
        if (null === $ormColumn) {
            return;
        }

        $columnName = $this->getColumnName($property, $ormColumn);
        if ('' === $columnName) {
            return;
        }

        $this->processIndexColumn($property, $cm, $columnName, $ormColumn);
        $this->processFulltextColumn($property, $cm, $columnName);
        $this->processUniqueColumn($property, $cm, $columnName);
    }

    private function getOrmColumn(\ReflectionProperty $property): ?ORM\Column
    {
        $ormColumns = $property->getAttributes(ORM\Column::class);
        if ([] === $ormColumns) {
            return null;
        }

        $inst = $ormColumns[0]->newInstance();
        assert($inst instanceof ORM\Column);

        return $inst;
    }

    private function getColumnName(\ReflectionProperty $property, ORM\Column $ormColumn): string
    {
        $name = $ormColumn->name;
        if (null === $name) {
            $name = $property->getName();
            $name = $this->inflector->toSnakeCase($name);
        }

        return $name;
    }

    /**
     * @param ORM\ClassMetadata<object> $cm
     */
    private function processIndexColumn(\ReflectionProperty $property, ORM\ClassMetadata $cm, string $columnName, ORM\Column $ormColumn): void
    {
        $indexColumns = $property->getAttributes(IndexColumn::class);
        if ([] === $indexColumns || $ormColumn->unique) {
            return;
        }

        $indexColumn = $indexColumns[0]->newInstance();
        $idxName = $indexColumn->name ?? $this->getIndexName($cm->table['name'], $columnName, 'idx');
        $cm->table['indexes'][$idxName] = [
            'columns' => [$columnName],
        ];
    }

    /**
     * @param ORM\ClassMetadata<object> $cm
     */
    private function processFulltextColumn(\ReflectionProperty $property, ORM\ClassMetadata $cm, string $columnName): void
    {
        $fulltextColumns = $property->getAttributes(FulltextColumn::class);
        if ([] === $fulltextColumns) {
            return;
        }

        $fulltextColumn = $fulltextColumns[0]->newInstance();
        $idxName = $fulltextColumn->name ?? $this->getIndexName($cm->table['name'], $columnName, 'fulltext');
        $cm->table['indexes'][$idxName] = [
            'columns' => [$columnName],
            'flags' => ['fulltext'],
        ];
    }

    /**
     * @param ORM\ClassMetadata<object> $cm
     */
    private function processUniqueColumn(\ReflectionProperty $property, ORM\ClassMetadata $cm, string $columnName): void
    {
        $uniqueColumns = $property->getAttributes(UniqueColumn::class);
        if ([] === $uniqueColumns) {
            return;
        }

        $uniqueColumn = $uniqueColumns[0]->newInstance();
        $idxName = $this->getIndexName($cm->table['name'], $columnName, 'unique');
        $cm->table['uniqueConstraints'][$idxName] = [
            'columns' => [$columnName],
        ];
    }
}
