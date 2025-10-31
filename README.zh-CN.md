# Doctrine Indexed Bundle

[English](README.md) | [中文](README.zh-CN.md)

[![Latest Version](https://img.shields.io/packagist/v/tourze/doctrine-indexed-bundle.svg?style=flat-square)](https://packagist.org/packages/tourze/doctrine-indexed-bundle)
[![Total Downloads](https://img.shields.io/packagist/dt/tourze/doctrine-indexed-bundle.svg?style=flat-square)](https://packagist.org/packages/tourze/doctrine-indexed-bundle)
[![PHP Version](https://img.shields.io/badge/php-%5E8.1-blue?style=flat-square)](https://www.php.net)
[![License](https://img.shields.io/packagist/l/tourze/doctrine-indexed-bundle.svg?style=flat-square)](LICENSE)
[![Build Status](https://img.shields.io/github/actions/workflow/status/tourze/packages/ci.yml?style=flat-square)](https://github.com/tourze/packages)
[![Coverage](https://img.shields.io/codecov/c/github/tourze/packages?style=flat-square)](https://codecov.io/gh/tourze/packages)

一个增强 Doctrine ORM 自动索引管理的 Symfony Bundle。

## 特性

- 自动为指定的实体属性添加索引
- 支持普通索引、全文索引和唯一索引
- 智能的索引命名策略，自动处理长表名
- 基于 PHP 属性（Attribute）配置，无需额外配置文件
- 零配置即可开箱即用

## 环境要求

- PHP 8.1 或更高版本
- Symfony 6.4 或更高版本
- Doctrine Bundle 2.13 或更高版本

## 安装

```bash
composer require tourze/doctrine-indexed-bundle
```

## 使用方法

1. 在 `config/bundles.php` 中注册 Bundle：

```php
return [
    // ...
    Tourze\DoctrineIndexedBundle\DoctrineIndexedBundle::class => ['all' => true],
];
```

2. 使用属性标记需要索引的实体字段：

```php
use Tourze\DoctrineIndexedBundle\Attribute\IndexColumn;
use Tourze\DoctrineIndexedBundle\Attribute\FulltextColumn;
use Tourze\DoctrineIndexedBundle\Attribute\UniqueColumn;

class YourEntity
{
    #[ORM\Column]
    #[IndexColumn] // 普通索引
    private string $name;

    #[ORM\Column(type: 'text')]
    #[FulltextColumn] // 全文索引
    private string $description;

    #[ORM\Column]
    #[UniqueColumn] // 唯一索引
    private string $email;
}
```

## 配置

该 Bundle 无需配置即可开箱即用。不过，您可以通过为属性提供 `name` 参数来自定义索引名称：

```php
#[IndexColumn(name: 'custom_index_name')]
private string $field;
```

## 索引命名规则

Bundle 会自动按照以下模式生成索引名称：

- 普通索引：`{表名}_idx_{字段名}`
- 全文索引：`{表名}_fulltext_{字段名}`
- 唯一约束：`{表名}_unique_{字段名}`

如果生成的名称超过最大长度（64个字符），Bundle 会使用 MD5 哈希来创建更短的名称，同时保持唯一性。

## 高级用法

### 自定义索引名称

您可以为索引指定自定义名称：

```php
#[IndexColumn(name: 'search_idx')]
private string $searchable;

#[FulltextColumn(name: 'content_fulltext')]
private string $content;

#[UniqueColumn(name: 'email_unique')]
private string $email;
```

### 多重属性

您可以在同一个实体上组合使用不同的索引类型：

```php
class Product
{
    #[ORM\Column]
    #[IndexColumn] // 用于搜索
    #[UniqueColumn] // 用于唯一性
    private string $sku;

    #[ORM\Column(type: 'text')]
    #[FulltextColumn] // 用于全文搜索
    #[IndexColumn] // 同时也用于普通搜索
    private string $description;
}
```

## 工作原理

- 通过监听 Doctrine 的 `loadClassMetadata` 事件，自动扫描实体的属性
- 检查属性上是否有 `IndexColumn`、`FulltextColumn` 或 `UniqueColumn` 
  注解
- 根据注解类型自动为实体字段添加索引
- 命名规则自动处理，兼容不同数据库的索引名长度限制

## 贡献指南

欢迎提交 Issue 和 PR 改进本 Bundle。

## 许可证

MIT License
