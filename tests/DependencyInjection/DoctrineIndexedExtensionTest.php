<?php

namespace Tourze\DoctrineIndexedBundle\Tests\DependencyInjection;

use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Tourze\DoctrineIndexedBundle\DependencyInjection\DoctrineIndexedExtension;
use Tourze\DoctrineIndexedBundle\EventSubscriber\AddIndexedListener;

/**
 * @covers \Tourze\DoctrineIndexedBundle\DependencyInjection\DoctrineIndexedExtension
 */
class DoctrineIndexedExtensionTest extends TestCase
{
    private DoctrineIndexedExtension $extension;
    private ContainerBuilder $container;

    protected function setUp(): void
    {
        $this->extension = new DoctrineIndexedExtension();
        $this->container = new ContainerBuilder();
    }

    public function testLoad(): void
    {
        $this->extension->load([], $this->container);

        // 验证扩展已经加载了配置
        $this->assertNotEmpty($this->container->getDefinitions());
        
        // 验证 Inflector 服务已被加载
        $this->assertTrue($this->container->has('Yiisoft\Strings\Inflector'));
    }
}