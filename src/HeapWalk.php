<?php

declare(strict_types=1);

namespace Asmblah\HeapWalk;

use Asmblah\HeapWalk\Result\Path\PathSetInterface;
use Asmblah\HeapWalk\Root\GlobalRootProvider;
use Asmblah\HeapWalk\Walker\ArrayWalker;
use Asmblah\HeapWalk\Walker\HeapWalker;
use Asmblah\HeapWalk\Walker\InstanceWalker;
use Asmblah\HeapWalk\Walker\ValueWalker;

/**
 * Class HeapWalk.
 *
 * Top-level entrypoint class for the heap walker service. See README.md for usage.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
class HeapWalk
{
    /**
     * @var GlobalRootProvider
     */
    private $rootProvider;
    /**
     * @var ValueWalker
     */
    private $valueWalker;

    public function __construct(?int $maxDepth = null)
    {
        $arrayWalker = new ArrayWalker();
        $instanceWalker = new InstanceWalker();
        $this->valueWalker = new ValueWalker($arrayWalker, $instanceWalker, $maxDepth);
        $arrayWalker->setValueWalker($this->valueWalker);
        $instanceWalker->setValueWalker($this->valueWalker);

        /** @noinspection PotentialMalwareInspection */
        $this->rootProvider = new GlobalRootProvider(
            $GLOBALS,
            // Note that internal functions are ignored.
            get_defined_functions()['user'],
            get_declared_classes()
        );
    }

    /**
     * @param string[] $fqcns
     * @return PathSetInterface[]
     */
    public function getInstancePathSets(array $fqcns): array
    {
        // Fetch the latest roots at this point so that we're as up to date as possible.
        $heapWalker = new HeapWalker($this->valueWalker, $this->rootProvider->getRoots());

        return $heapWalker->getInstancePathSets($fqcns);
    }
}
