<?php

declare(strict_types=1);

namespace Asmblah\HeapWalk\Result\Path;

use Asmblah\HeapWalk\Result\ResultInterface;

/**
 * Interface PathSetCollectionInterface.
 *
 * Path set collections group all path sets discovered as a result of a heap walk.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
interface PathSetCollectionInterface extends ResultInterface
{
    /**
     * Diffs this collection against the given other collection, returning a new PathSetCollection
     * that contains only the sets from this collection that are not present in the given one.
     *
     * @param PathSetCollectionInterface $otherPathSetCollection
     * @return PathSetCollectionInterface
     */
    public function diff(PathSetCollectionInterface $otherPathSetCollection): PathSetCollectionInterface;

    /**
     * Fetches all sets in this collection.
     *
     * @return PathSetInterface[]
     */
    public function getPathSets(): array;
}
