<?php

declare(strict_types=1);

namespace Asmblah\HeapWalk\Walker;

use Asmblah\HeapWalk\Result\Path\Descension\DescensionInterface;
use RuntimeException;

/**
 * Class ValueWalker.
 *
 * Walks the given value, using ArrayWalker and InstanceWalker for recursion as applicable.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
class ValueWalker
{
    public const DEFAULT_MAX_DEPTH = 100;
    /**
     * @var ArrayWalker
     */
    private $arrayWalker;
    /**
     * @var InstanceWalker
     */
    private $instanceWalker;
    /**
     * @var int
     */
    private $maxDepth;

    /**
     * @param ArrayWalker $arrayWalker
     * @param InstanceWalker $instanceWalker
     * @param int $maxDepth
     */
    public function __construct(
        ArrayWalker $arrayWalker,
        InstanceWalker $instanceWalker,
        ?int $maxDepth = null
    ) {
        $this->arrayWalker = $arrayWalker;
        $this->instanceWalker = $instanceWalker;
        $this->maxDepth = $maxDepth ?? self::DEFAULT_MAX_DEPTH;
    }

    /**
     * @param mixed $value
     * @param callable $visitor
     * @param DescensionInterface[] $descensions
     */
    public function walkValue($value, callable $visitor, array $descensions): void
    {
        $depth = count($descensions);

        // Catch any unhandled infinite recursion.
        if ($depth >= $this->maxDepth) {
            throw new RuntimeException(sprintf('Limit of %d reached (depth is %d)', $this->maxDepth, $depth));
        }

        if ($visitor($value, $descensions) === false) {
            // Visitor has specified not to recurse into this value.
            return;
        }

        if (is_array($value)) {
            if ($this->alreadyProcessed($value, $descensions)) {
                // We have already processed this array for this descent, avoid infinite recursion.
                return;
            }

            $this->arrayWalker->walkArray($value, $visitor, $descensions);
        } elseif (is_object($value)) {
            if ($this->alreadyProcessed($value, $descensions)) {
                // We have already processed this instance for this descent, avoid infinite recursion.
                return;
            }

            $this->instanceWalker->walkInstance($value, $visitor, $descensions);
        }
    }

    /**
     * Determines whether the given value has already been processed during this descent.
     *
     * @param mixed $value
     * @param array $descensions
     * @return bool
     */
    private function alreadyProcessed($value, array $descensions): bool
    {
        // Note that we exclude the last (most recent) descent as it will not yet have been walked into.
        for ($descensionIndex = 0, $count = count($descensions) - 1; $descensionIndex < $count; $descensionIndex++) {
            $descension = $descensions[$descensionIndex];

            if ($descension->getValue() === $value) {
                return true;
            }
        }

        return false;
    }
}
