<?php

declare(strict_types=1);

namespace Asmblah\HeapWalk\Result\Path\Descension\Root;

/**
 * Class GlobalVariableRoot.
 *
 * Represents a global variable, which is a root point to start scanning recursively from.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
class GlobalVariableRoot implements RootInterface
{
    /**
     * @var string
     */
    private $name;
    /**
     * @var mixed
     */
    private $value;

    /**
     * @param string $name
     * @param mixed $value
     */
    public function __construct(string $name, $value)
    {
        $this->name = $name;
        $this->value = $value;
    }
    
    /**
     * @inheritDoc
     */
    public function getName(): string
    {
        return $this->name;
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
        return ['name' => $this->name, 'value' => $this->value];
    }

    /**
     * @inheritDoc
     */
    public function toString(): string
    {
        return '$GLOBALS[' . $this->name . ']';
    }
}
