<?php

declare(strict_types=1);

namespace Asmblah\HeapWalk\Result\Path\Descension\Root;

/**
 * Class StaticPropertyRoot.
 *
 * Represents a static property of a class, which is a root point to start scanning recursively from.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
class StaticPropertyRoot implements RootInterface
{
    /**
     * @var string
     */
    private $className;
    /**
     * @var string
     */
    private $propertyName;
    /**
     * @var mixed
     */
    private $value;

    /**
     * @param string $className
     * @param string $propertyName
     * @param mixed $value
     */
    public function __construct(string $className, string $propertyName, $value)
    {
        $this->className = $className;
        $this->propertyName = $propertyName;
        $this->value = $value;
    }

    /**
     * @inheritDoc
     */
    public function getName(): string
    {
        return $this->propertyName;
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
        return ['class' => $this->className, 'property' => $this->propertyName, 'value' => $this->value];
    }

    /**
     * @inheritDoc
     */
    public function toString(): string
    {
        return $this->className . '::$' . $this->propertyName;
    }
}
