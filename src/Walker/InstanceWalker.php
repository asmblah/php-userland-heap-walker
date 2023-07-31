<?php

declare(strict_types=1);

namespace Asmblah\HeapWalk\Walker;

use Asmblah\HeapWalk\Result\Path\Descension\ClosureBoundObjectDescension;
use Asmblah\HeapWalk\Result\Path\Descension\ClosureInheritedVariableDescension;
use Asmblah\HeapWalk\Result\Path\Descension\DescensionInterface;
use Asmblah\HeapWalk\Result\Path\Descension\InstancePropertyDescension;
use Closure;
use ReflectionClass;
use ReflectionFunction;
use ReflectionObject;
use ReflectionProperty;
use RuntimeException;
use Throwable;

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
        $reflectionClassOrObject = new ReflectionObject($instance);

        // Walk all properties, regardless of visibility (private, protected & public)
        // up the entire class hierarchy for the object.
        do {
            $properties = $reflectionClassOrObject->getProperties(
                // Exclude static properties.
                ReflectionProperty::IS_PRIVATE |
                ReflectionProperty::IS_PROTECTED |
                ReflectionProperty::IS_PUBLIC
            );

            foreach ($properties as $reflectionProperty) {
                if ($reflectionProperty->getDeclaringClass()->getName() !== $reflectionClassOrObject->getName()) {
                    // Ignore properties that are not declared by the class currently being handled
                    // (e.g. inherited public properties).
                    continue;
                }

                $this->walkProperty(
                    $reflectionClassOrObject,
                    $instance,
                    $reflectionProperty,
                    $visitor,
                    $descensions
                );
            }
        } while (($reflectionClassOrObject = $reflectionClassOrObject->getParentClass()) !== false);

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
     * @param ReflectionClass $reflectionClass
     * @param object $instance
     * @param ReflectionProperty $reflectionProperty
     * @param callable $visitor
     * @param DescensionInterface[] $descensions
     */
    private function walkProperty(
        ReflectionClass $reflectionClass,
        object $instance,
        ReflectionProperty $reflectionProperty,
        callable $visitor,
        array $descensions
    ): void {
        $propertyName = $reflectionProperty->getName();

        if ($reflectionClass->isInternal()) {
            // Ignore visibility.
            $reflectionProperty->setAccessible(true);

            $propertyValue = $reflectionProperty->getValue($instance);
        } else {
            /*
             * NB: Private properties are hidden from sub-classes, if a subclass defines __get()/__set()
             *     those will be called even if ->setAccessible(true) was used, so we bind
             *     a closure to the property's owning class instead.
             */
            try {
                $propertyValue = (function () use ($propertyName) {
                    // Accommodate properties that were declared by the class but unset on the instance.
                    return $this->$propertyName ?? null;
                })->bindTo($instance, $reflectionClass->getName())();
            } catch (Throwable $throwable) {
                throw new RuntimeException('Unexpected throw while reading property', 0, $throwable);
            }
        }

        $this->valueWalker->walkValue(
            $propertyValue,
            $visitor,
            array_merge(
                $descensions,
                [new InstancePropertyDescension($instance, $propertyName, $propertyValue)]
            )
        );
    }

    /**
     * @param ValueWalker $valueWalker
     */
    public function setValueWalker(ValueWalker $valueWalker): void
    {
        $this->valueWalker = $valueWalker;
    }
}
