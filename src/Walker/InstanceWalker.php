<?php

declare(strict_types=1);

namespace Asmblah\HeapWalk\Walker;

use Asmblah\HeapWalk\Result\Path\Descension\ClosureBoundObjectDescension;
use Asmblah\HeapWalk\Result\Path\Descension\ClosureInheritedVariableDescension;
use Asmblah\HeapWalk\Result\Path\Descension\DescensionInterface;
use Asmblah\HeapWalk\Result\Path\Descension\InstancePropertyDescension;
use Closure;
use ReflectionFunction;
use ReflectionObject;

/**
 * Class InstanceWalker.
 *
 * Walks the given object, using ValueWalker for recursion.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
class InstanceWalker
{
    /**
     * @var ValueWalker
     */
    private $valueWalker;

    /**
     * @param object $instance
     * @param callable $visitor
     * @param DescensionInterface[] $descensions
     */
    public function walkInstance(object $instance, callable $visitor, array $descensions = []): void
    {
        $reflectionObject = new ReflectionObject($instance);

        // Walk all properties, regardless of visibility (private, protected & public).
        foreach ($reflectionObject->getProperties() as $reflectionProperty) {
            $reflectionProperty->setAccessible(true); // Ignore property visibility.

            $propertyValue = $reflectionProperty->getValue($instance);

            $this->valueWalker->walkValue(
                $propertyValue,
                $visitor,
                array_merge(
                    $descensions,
                    [new InstancePropertyDescension($instance, $reflectionProperty->getName(), $propertyValue)]
                )
            );
        }

        if ($instance instanceof Closure) {
            $reflectionFunction = new ReflectionFunction($instance);

            // Check the closure's bound $this object, if set.
            $thisObject = $reflectionFunction->getClosureThis();

            if ($thisObject !== null) {
                $this->valueWalker->walkValue(
                    $thisObject,
                    $visitor,
                    array_merge(
                        $descensions,
                        [new ClosureBoundObjectDescension($instance, $thisObject)]
                    )
                );
            }

            // Check the closure's inherited variables (inside the use(...) clause).
            foreach ($reflectionFunction->getStaticVariables() as $variableName => $variableValue) {
                $this->valueWalker->walkValue(
                    $variableValue,
                    $visitor,
                    array_merge(
                        $descensions,
                        [new ClosureInheritedVariableDescension($instance, $variableName, $variableValue)]
                    )
                );
            }
        }
    }

    /**
     * @param ValueWalker $valueWalker
     */
    public function setValueWalker(ValueWalker $valueWalker): void
    {
        $this->valueWalker = $valueWalker;
    }
}
