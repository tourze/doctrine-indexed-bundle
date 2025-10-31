<?php

namespace Tourze\DoctrineIndexedBundle\Tests\Fixtures;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Tourze\DoctrineIndexedBundle\Attribute\FulltextColumn;
use Tourze\DoctrineIndexedBundle\Attribute\IndexColumn;
use Tourze\DoctrineIndexedBundle\Attribute\UniqueColumn;

/**
 * 测试实体类，用于验证索引属性功能
 *
 * @internal
 */
#[ORM\Entity]
#[ORM\Table(name: 'test_entity')]
class TestEntity
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    private ?int $id = null;

    #[IndexColumn]
    #[ORM\Column(type: Types::STRING, length: 255)]
    private ?string $indexedField = null;

    #[FulltextColumn]
    #[ORM\Column(type: Types::TEXT)]
    private ?string $fulltextField = null;

    #[IndexColumn(name: 'custom_index_name')]
    #[ORM\Column(type: Types::STRING, length: 255)]
    private ?string $customIndexField = null;

    #[UniqueColumn]
    #[ORM\Column(type: Types::STRING, length: 255)]
    private ?string $uniqueField = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getIndexedField(): ?string
    {
        return $this->indexedField;
    }

    public function setIndexedField(?string $indexedField): self
    {
        $this->indexedField = $indexedField;

        return $this;
    }

    public function getFulltextField(): ?string
    {
        return $this->fulltextField;
    }

    public function setFulltextField(?string $fulltextField): self
    {
        $this->fulltextField = $fulltextField;

        return $this;
    }

    public function getCustomIndexField(): ?string
    {
        return $this->customIndexField;
    }

    public function setCustomIndexField(?string $customIndexField): self
    {
        $this->customIndexField = $customIndexField;

        return $this;
    }

    public function getUniqueField(): ?string
    {
        return $this->uniqueField;
    }

    public function setUniqueField(?string $uniqueField): self
    {
        $this->uniqueField = $uniqueField;

        return $this;
    }
}
