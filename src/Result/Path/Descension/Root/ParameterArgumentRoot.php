<?php

declare(strict_types=1);

namespace Asmblah\HeapWalk\Result\Path\Descension\Root;

/**
 * Class ParameterArgumentRoot.
 *
 * Represents an argument for a parameter, which is a root point to start scanning recursively from.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
class ParameterArgumentRoot implements RootInterface
{
    /**
     * @var string
     */
    private $function;
    /**
     * @var mixed
     */
    private $value;

    /**
     * @param string $function
     * @param mixed $value
     */
    public function __construct(string $function, $value)
    {
        $this->function = $function;
        $this->value = $value;
    }

    /**
     * @inheritDoc
     */
    public function getName(): string
    {
        return $this->function . '(<arg>)';
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
        return ['function' => $this->function, 'value' => $this->value];
    }

    /**
     * @inheritDoc
     */
    public function toString(): string
    {
        return $this->function . '(<arg>)';
    }
}
