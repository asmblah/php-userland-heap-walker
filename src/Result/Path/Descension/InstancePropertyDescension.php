<?php

declare(strict_types=1);

namespace Asmblah\HeapWalk\Result\Path\Descension;

use Asmblah\HeapWalk\Result\ClassTools;

/**
 * Class InstancePropertyDescension.
 *
 * Represents a reference from an object to an instance property.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
class InstancePropertyDescension implements DescensionInterface
{
    /**
     * @var object
     */
    private $instance;
    /**
     * @var string
     */
    private $propertyName;
    /**
     * @var mixed
     */
    private $propertyValue;

    /**
     * @param object $instance
     * @param string $propertyName
     * @param mixed $propertyValue
     */
    public function __construct(object $instance, string $propertyName, $propertyValue)
    {
        $this->instance = $instance;
        $this->propertyName = $propertyName;
        $this->propertyValue = $propertyValue;
    }

    /**
     * @return object
     */
    public function getInstance(): object
    {
        return $this->instance;
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
        return $this->propertyValue;
    }

    /**
     * @inheritDoc
     */
    public function toArray(): array
    {
        return ['class' => ClassTools::toReadableClassName(get_class($this->instance)), 'property' => $this->propertyName];
    }

    /**
     * @inheritDoc
     */
    public function toString(): string
    {
        return '->(' . ClassTools::toReadableClassName(get_class($this->instance)) . '::$' . $this->propertyName . ')';
    }
}
