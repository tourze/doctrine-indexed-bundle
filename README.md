# Doctrine Indexed Bundle

[English](README.md) | [中文](README.zh-CN.md)

[![Latest Version](https://img.shields.io/packagist/v/tourze/doctrine-indexed-bundle.svg?style=flat-square)](https://packagist.org/packages/tourze/doctrine-indexed-bundle)
[![Total Downloads](https://img.shields.io/packagist/dt/tourze/doctrine-indexed-bundle.svg?style=flat-square)](https://packagist.org/packages/tourze/doctrine-indexed-bundle)
[![PHP Version](https://img.shields.io/badge/php-%5E8.1-blue?style=flat-square)](https://www.php.net)
[![License](https://img.shields.io/packagist/l/tourze/doctrine-indexed-bundle.svg?style=flat-square)](LICENSE)
[![Build Status](https://img.shields.io/github/actions/workflow/status/tourze/packages/ci.yml?style=flat-square)](https://github.com/tourze/packages)
[![Coverage](https://img.shields.io/codecov/c/github/tourze/packages?style=flat-square)](https://codecov.io/gh/tourze/packages)

A Symfony bundle that enhances Doctrine ORM with automatic index management.

## Features

- Automatically adds indexes to specified entity properties
- Supports regular, fulltext, and unique indexes
- Smart index naming strategy, automatically handles long table names
- Attribute-based configuration (PHP 8 attributes), no extra config files
- Zero configuration required, works out-of-the-box

## Requirements

- PHP 8.1 or higher
- Symfony 6.4 or higher
- Doctrine Bundle 2.13 or higher

## Installation

```bash
composer require tourze/doctrine-indexed-bundle
```

## Usage

1. Register the bundle in `config/bundles.php`:

```php
return [
    // ...
    Tourze\DoctrineIndexedBundle\DoctrineIndexedBundle::class => ['all' => true],
];
```

2. Use attributes to mark entity fields that require indexes:

```php
use Tourze\DoctrineIndexedBundle\Attribute\IndexColumn;
use Tourze\DoctrineIndexedBundle\Attribute\FulltextColumn;
use Tourze\DoctrineIndexedBundle\Attribute\UniqueColumn;

class YourEntity
{
    #[ORM\Column]
    #[IndexColumn] // Regular index
    private string $name;

    #[ORM\Column(type: 'text')]
    #[FulltextColumn] // Fulltext index
    private string $description;

    #[ORM\Column]
    #[UniqueColumn] // Unique index
    private string $email;
}
```

## Configuration

This bundle requires no configuration and works out-of-the-box. However, 
you can customize index names by providing a `name` parameter to the attributes:

```php
#[IndexColumn(name: 'custom_index_name')]
private string $field;
```

## Index Naming Convention

The bundle automatically generates index names as follows:

- Regular index: `{table_name}_idx_{column_name}`
- Fulltext index: `{table_name}_fulltext_{column_name}`
- Unique constraint: `{table_name}_unique_{column_name}`

If the generated name exceeds the maximum length (64 characters), the bundle 
will use MD5 hashing to create a shorter name while maintaining uniqueness.

## Advanced Usage

### Custom Index Names

You can specify custom names for indexes:

```php
#[IndexColumn(name: 'search_idx')]
private string $searchable;

#[FulltextColumn(name: 'content_fulltext')]
private string $content;

#[UniqueColumn(name: 'email_unique')]
private string $email;
```

### Multiple Attributes

You can combine different index types on the same entity:

```php
class Product
{
    #[ORM\Column]
    #[IndexColumn] // For searching
    #[UniqueColumn] // For uniqueness
    private string $sku;

    #[ORM\Column(type: 'text')]
    #[FulltextColumn] // For full-text search
    #[IndexColumn] // For regular search too
    private string $description;
}
```

## How It Works

- Listens to Doctrine's `loadClassMetadata` event and scans entity properties
- Checks for `IndexColumn`, `FulltextColumn`, or `UniqueColumn` attributes 
  on properties
- Automatically adds the corresponding index to the entity field
- Naming strategy ensures compatibility with DBMS index name length limits

## Contributing

Feel free to submit issues and pull requests to help improve this bundle.

## License

MIT License
