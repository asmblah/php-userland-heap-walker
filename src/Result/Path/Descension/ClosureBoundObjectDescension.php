<?php

declare(strict_types=1);

namespace Asmblah\HeapWalk\Result\Path\Descension;

use Closure;

/**
 * Class ClosureBoundObjectDescension.
 *
 * Represents a reference down from a closure to its $this object, if specified.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
class ClosureBoundObjectDescension implements DescensionInterface
{
    /**
     * @var Closure
     */
    private $closure;
    /**
     * @var mixed
     */
    private $thisValue;

    /**
     * @param Closure $closure
     * @param mixed $thisValue
     */
    public function __construct(Closure $closure, $thisValue)
    {
        $this->closure = $closure;
        $this->thisValue = $thisValue;
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
        return 'this';
    }

    /**
     * @inheritDoc
     */
    public function getValue()
    {
        return $this->thisValue;
    }

    /**
     * @inheritDoc
     */
    public function toArray(): array
    {
        return ['this' => $this->thisValue];
    }

    /**
     * @inheritDoc
     */
    public function toString(): string
    {
        return '->(Closure::$this)';
    }
}
