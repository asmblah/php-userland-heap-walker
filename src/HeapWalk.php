<?php

declare(strict_types=1);

namespace Asmblah\HeapWalk;

use Asmblah\HeapWalk\Result\Path\PathSetInterface;
use Asmblah\HeapWalk\Root\DelegatingRootProvider;
use Asmblah\HeapWalk\Root\RootProviderFactory;
use Asmblah\HeapWalk\Root\RootProviderFactoryInterface;
use Asmblah\HeapWalk\Root\RootProviderInterface;
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
     * @var DelegatingRootProvider
     */
    private $delegatingRootProvider;
    /**
     * @var ValueWalker
     */
    private $valueWalker;

    /**
     * @param int|null $maxDepth Maximum depth to recurse to. If exceeded, an exception will be raised.
     * @param RootProviderFactoryInterface|null $rootProviderFactory
     */
    public function __construct(?int $maxDepth = null, ?RootProviderFactoryInterface $rootProviderFactory = null)
    {
        $arrayWalker = new ArrayWalker();
        $instanceWalker = new InstanceWalker();
        $this->valueWalker = new ValueWalker($arrayWalker, $instanceWalker, $maxDepth);
        $arrayWalker->setValueWalker($this->valueWalker);
        $instanceWalker->setValueWalker($this->valueWalker);

        $this->delegatingRootProvider = ($rootProviderFactory ?? new RootProviderFactory())->createProvider();
    }

    /**
     * Registers a custom provider of roots to search for accessible values.
     *
     * @param RootProviderInterface $rootProvider
     */
    public function registerRootProvider(RootProviderInterface $rootProvider): void
    {
        $this->delegatingRootProvider->registerProvider($rootProvider);
    }

    /**
     * Fetches all instances of the given Fully-Qualified Class Names given, with one InstancePathSet
     * per instance found. Each InstancePathSet will contain all paths that lead to the instance from a root.
     *
     * @param string[] $fqcns
     * @return PathSetInterface[]
     */
    public function getInstancePathSets(array $fqcns): array
    {
        // Fetch the latest roots at this point so that we're as up-to-date as possible.
        $heapWalker = new HeapWalker($this->valueWalker, $this->delegatingRootProvider->getRoots());

        return $heapWalker->getInstancePathSets($fqcns);
    }
}
