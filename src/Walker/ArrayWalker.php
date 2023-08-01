<?php

declare(strict_types=1);

namespace Asmblah\HeapWalk\Walker;

use Asmblah\HeapWalk\Result\Path\Descension\ArrayElementDescension;
use Asmblah\HeapWalk\Result\Path\Descension\DescensionInterface;

/**
 * Class ArrayWalker.
 *
 * Walks the given array, using ValueWalker for recursion.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
class ArrayWalker
{
    /**
     * @var ValueWalker
     */
    private $valueWalker;

    /**
     * Walks the given array, using ValueWalker for recursion.
     *
     * @param array $array
     * @param callable $visitor
     * @param DescensionInterface[] $descensions
     */
    public function walkArray(array $array, callable $visitor, array $descensions): void
    {
        foreach ($array as $elementKey => $elementValue) {
            $this->valueWalker->walkValue(
                $elementValue,
                $visitor,
                array_merge($descensions, [new ArrayElementDescension($array, $elementKey, $elementValue)])
            );
        }
    }

    /**
     * Injects the ValueWalker service (cannot inject via constructor due to circular dependency).
     *
     * @param ValueWalker $valueWalker
     */
    public function setValueWalker(ValueWalker $valueWalker): void
    {
        $this->valueWalker = $valueWalker;
    }
}
