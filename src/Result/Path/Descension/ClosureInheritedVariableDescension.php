<?php

declare(strict_types=1);

namespace Asmblah\HeapWalk\Result\Path\Descension;

use Closure;

/**
 * Class ClosureInheritedVariableDescension.
 *
 * Represents a reference down from a closure to its "use (...)" variables.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
class ClosureInheritedVariableDescension implements DescensionInterface
{
    /**
     * @var Closure
     */
    private $closure;
    /**
     * @var string
     */
    private $variableName;
    /**
     * @var mixed
     */
    private $variableValue;

    /**
     * @param Closure $closure
     * @param string $variableName
     * @param mixed $variableValue
     */
    public function __construct(Closure $closure, string $variableName, $variableValue)
    {
        $this->closure = $closure;
        $this->variableName = $variableName;
        $this->variableValue = $variableValue;
    }

    /**
     * @return Closure
     */
    public function getClosure(): Closure
    {
        return $this->closure;
    }

    /**
     * @inheritDoc
     */
    public function getName(): string
    {
        return $this->variableName;
    }

    /**
     * @inheritDoc
     */
    public function getValue()
    {
        return $this->variableValue;
    }

    /**
     * @inheritDoc
     */
    public function toArray(): array
    {
        return ['name' => $this->variableName, 'value' => $this->variableValue];
    }

    /**
     * @inheritDoc
     */
    public function toString(): string
    {
        return ' (Closure) use ($' . $this->variableName . ')';
    }
}
