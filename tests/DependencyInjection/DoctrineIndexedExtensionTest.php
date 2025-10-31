<?php

declare(strict_types=1);

namespace Tourze\DoctrineIndexedBundle\Tests\DependencyInjection;

use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\DoctrineIndexedBundle\DependencyInjection\DoctrineIndexedExtension;
use Tourze\PHPUnitSymfonyUnitTest\AbstractDependencyInjectionExtensionTestCase;

/**
 * @internal
 */
#[CoversClass(DoctrineIndexedExtension::class)]
final class DoctrineIndexedExtensionTest extends AbstractDependencyInjectionExtensionTestCase
{
}
