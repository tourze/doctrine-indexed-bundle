<?php

namespace Tourze\DoctrineIndexedBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Tourze\BundleDependency\BundleDependencyInterface;

class DoctrineIndexedBundle extends Bundle implements BundleDependencyInterface
{
    public static function getBundleDependencies(): array
    {
        return [
            \Doctrine\Bundle\DoctrineBundle\DoctrineBundle::class => ['all' => true],
        ];
    }
}
