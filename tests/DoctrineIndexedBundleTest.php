<?php

namespace Tourze\DoctrineIndexedBundle\Tests;

use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Tourze\DoctrineIndexedBundle\DoctrineIndexedBundle;

/**
 * DoctrineIndexedBundle主类测试
 */
class DoctrineIndexedBundleTest extends TestCase
{
    /**
     * 测试Bundle类是否正确继承Symfony的Bundle基类
     */
    public function testBundleInheritance(): void
    {
        $bundle = new DoctrineIndexedBundle();
        $this->assertInstanceOf(Bundle::class, $bundle);
    }
}
