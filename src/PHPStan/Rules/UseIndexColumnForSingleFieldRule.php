<?php

declare(strict_types=1);

namespace Tourze\DoctrineIndexedBundle\PHPStan\Rules;

use Doctrine\ORM\Mapping\Index;
use PhpParser\Node;
use PhpParser\Node\Attribute;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use Tourze\DoctrineIndexedBundle\Attribute\IndexColumn;

/**
 * @implements Rule<Attribute>
 */
class UseIndexColumnForSingleFieldRule implements Rule
{
    public function getNodeType(): string
    {
        return Attribute::class;
    }

    public function processNode(Node $node, Scope $scope): array
    {
        if (!$node instanceof Attribute) {
            return [];
        }

        $attributeClassName = $node->name->toString();

        if (Index::class !== $attributeClassName) {
            return [];
        }

        $args = $node->args;

        foreach ($args as $arg) {
            if ('columns' === $arg->name?->toString()) {
                if ($arg->value instanceof Node\Expr\Array_ && 1 === count($arg->value->items)) {
                    return [
                        RuleErrorBuilder::message(
                            sprintf(
                                'Using ORM\Index with a single column is discouraged. Consider using %s instead.',
                                IndexColumn::class
                            )
                        )->build(),
                    ];
                }
            }
        }

        return [];
    }
}
