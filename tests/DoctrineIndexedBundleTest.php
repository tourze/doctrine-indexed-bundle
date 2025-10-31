<?php

declare(strict_types=1);

namespace Tourze\DoctrineIndexedBundle\Tests;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\DoctrineIndexedBundle\DoctrineIndexedBundle;
use Tourze\PHPUnitSymfonyKernelTest\AbstractBundleTestCase;

/**
 * @internal
 */
#[CoversClass(DoctrineIndexedBundle::class)]
#[RunTestsInSeparateProcesses]
final class DoctrineIndexedBundleTest extends AbstractBundleTestCase
{
}
