<?php

declare(strict_types=1);

namespace Tourze\DoctrineIndexedBundle\PHPStan\Rules;

use Doctrine\ORM\Mapping\Column;
use PhpParser\Node;
use PhpParser\Node\Stmt\Property;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use Tourze\DoctrineIndexedBundle\Attribute\IndexColumn;

/**
 * @implements Rule<Property>
 */
class IndexColumnRequiresColumnRule implements Rule
{
    public function getNodeType(): string
    {
        return Property::class;
    }

    public function processNode(Node $node, Scope $scope): array
    {
        if (!$node instanceof Property) {
            return [];
        }

        $hasIndexColumn = false;
        $hasColumn = false;

        foreach ($node->attrGroups as $attrGroup) {
            foreach ($attrGroup->attrs as $attr) {
                $attributeName = $attr->name->toString();
                if (IndexColumn::class === $attributeName) {
                    $hasIndexColumn = true;
                }
                if (Column::class === $attributeName) {
                    $hasColumn = true;
                }
            }
        }

        if ($hasIndexColumn && !$hasColumn) {
            $propertyName = $node->props[0]->name->toString();
            $className = $scope->getClassReflection()->getName();

            return [
                RuleErrorBuilder::message(
                    sprintf(
                        'Property "%s" in class "%s" has attribute "%s" but is missing attribute "%s".',
                        $propertyName,
                        $className,
                        IndexColumn::class,
                        Column::class
                    )
                )->build(),
            ];
        }

        return [];
    }
}
