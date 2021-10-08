<?php

declare(strict_types=1);

namespace Asmblah\HeapWalk\Root;

use Asmblah\HeapWalk\Result\Path\Descension\Root\RootInterface;
use ReflectionException;

/**
 * Interface RootProviderInterface.
 *
 * Discovers accessible roots from which to search for values.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
interface RootProviderInterface
{
    /**
     * Fetches root values to check recursively in order to walk the userland heap.
     * This should be mostly equivalent to the GC roots.
     *
     * @return RootInterface[]
     * @throws ReflectionException
     */
    public function getRoots(): array;
}
