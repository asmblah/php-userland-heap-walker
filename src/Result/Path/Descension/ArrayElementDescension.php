<?php

declare(strict_types=1);

namespace Asmblah\HeapWalk\Result\Path\Descension;

/**
 * Class ArrayElementDescension.
 *
 * Represents a reference down from an array to an element of that array.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
class ArrayElementDescension implements DescensionInterface
{
    /**
     * @var array
     */
    private $array;
    /**
     * @var string
     */
    private $elementKey;
    /**
     * @var mixed
     */
    private $elementValue;

    /**
     * @param array $array
     * @param int|float|string $elementKey
     * @param mixed $elementValue
     */
    public function __construct(array $array, $elementKey, $elementValue)
    {
        $this->array = $array;
        $this->elementKey = $elementKey;
        $this->elementValue = $elementValue;
    }

    /**
     * @return array
     */
    public function getArray(): array
    {
        return $this->array;
    }

    /**
     * @inheritDoc
     */
    public function getName(): string
    {
        return $this->elementKey;
    }

    /**
     * @inheritDoc
     */
    public function getValue()
    {
        return $this->elementValue;
    }

    /**
     * @inheritDoc
     */
    public function toArray(): array
    {
        return ['key' => $this->elementKey];
    }

    /**
     * @inheritDoc
     */
    public function toString(): string
    {
        return '[' . $this->elementKey . ']';
    }
}
