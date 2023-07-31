<?php

declare(strict_types=1);

namespace Asmblah\HeapWalk\Result\Path;

use Asmblah\HeapWalk\Result\ResultInterface;

/**
 * Interface PathSetInterface.
 *
 * Path sets group all discovered paths from roots to a value.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
interface PathSetInterface extends ResultInterface
{
    /**
     * Diffs this set against the given other set, returning a new PathSet
     * that contains only the values from this set that are not present in the given one.
     *
     * @param PathSetInterface $otherPathSet
     * @return PathSetInterface
     */
    public function diff(PathSetInterface $otherPathSet): PathSetInterface;

    /**
     * @return mixed
     */
    public function getEventualValue();

    /**
     * @return PathInterface[]
     */
    public function getPaths(): array;
}
