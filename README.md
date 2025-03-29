# Doctrine Indexed Bundle

[English](#english) | [中文](#中文)

## English

A Symfony bundle that enhances Doctrine ORM with automatic index management.

### Features

- Automatically adds indexes to specified entity properties
- Supports both regular indexes and fulltext indexes
- Smart index naming strategy that handles long table names
- Attribute-based configuration
- Zero configuration required

### Requirements

- PHP 8.1 or higher
- Symfony 6.4 or higher
- Doctrine Bundle 2.13 or higher

### Installation

```bash
composer require tourze/doctrine-indexed-bundle
```

### Usage

1. Add the bundle to your `config/bundles.php`:

```php
return [
    // ...
    Tourze\DoctrineIndexedBundle\DoctrineIndexedBundle::class => ['all' => true],
];
```

1. Use attributes to mark properties that need indexing:

```php
use Tourze\DoctrineIndexedBundle\Attribute\IndexColumn;
use Tourze\DoctrineIndexedBundle\Attribute\FulltextColumn;

class YourEntity
{
    #[ORM\Column]
    #[IndexColumn] // Regular index
    private string $name;

    #[ORM\Column(type: 'text')]
    #[FulltextColumn] // Fulltext index
    private string $description;
}
```

### Index Naming Convention

The bundle automatically generates index names following this pattern:

- Regular indexes: `{table_name}_idx_{column_name}`
- Fulltext indexes: `{table_name}_fulltext_{column_name}`

If the generated name exceeds the maximum length (64 characters), the bundle will use MD5 hashing to create a shorter name while maintaining uniqueness.

## 中文

一个增强 Doctrine ORM 自动索引管理的 Symfony Bundle。

### 特性

- 自动为指定的实体属性添加索引
- 支持普通索引和全文索引
- 智能的索引命名策略，处理长表名
- 基于属性的配置
- 零配置要求

### 要求

- PHP 8.1 或更高版本
- Symfony 6.4 或更高版本
- Doctrine Bundle 2.13 或更高版本

### 安装

```bash
composer require tourze/doctrine-indexed-bundle
```

### 使用方法

1. 在 `config/bundles.php` 中添加 bundle：

```php
return [
    // ...
    Tourze\DoctrineIndexedBundle\DoctrineIndexedBundle::class => ['all' => true],
];
```

1. 使用属性标记需要索引的字段：

```php
use Tourze\DoctrineIndexedBundle\Attribute\IndexColumn;
use Tourze\DoctrineIndexedBundle\Attribute\FulltextColumn;

class YourEntity
{
    #[ORM\Column]
    #[IndexColumn] // 普通索引
    private string $name;

    #[ORM\Column(type: 'text')]
    #[FulltextColumn] // 全文索引
    private string $description;
}
```

### 索引命名规则

Bundle 会自动按照以下模式生成索引名称：

- 普通索引：`{表名}_idx_{字段名}`
- 全文索引：`{表名}_fulltext_{字段名}`

如果生成的名称超过最大长度（64个字符），Bundle 会使用 MD5 哈希来创建更短的名称，同时保持唯一性。
