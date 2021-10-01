<?php

declare(strict_types=1);

namespace Asmblah\HeapWalk\Result\Path\Descension\Root;

/**
 * Class StaticVariableRoot.
 *
 * Represents a static variable of a function, which is a root point to start scanning recursively from.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
class StaticVariableRoot implements RootInterface
{
    /**
     * @var string
     */
    private $functionName;
    /**
     * @var mixed
     */
    private $value;
    /**
     * @var string
     */
    private $variableName;

    /**
     * @param string $functionName
     * @param string $variableName
     * @param mixed $value
     */
    public function __construct(string $functionName, string $variableName, $value)
    {
        $this->functionName = $functionName;
        $this->value = $value;
        $this->variableName = $variableName;
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
        return $this->value;
    }

    /**
     * @inheritDoc
     */
    public function toArray(): array
    {
        return ['function' => $this->functionName, 'name' => $this->variableName, 'value' => $this->value];
    }

    /**
     * @inheritDoc
     */
    public function toString(): string
    {
        return '(' . $this->functionName . '() static $' . $this->variableName . ')';
    }
}
