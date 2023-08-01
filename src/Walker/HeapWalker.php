<?php

declare(strict_types=1);

namespace Asmblah\HeapWalk\Walker;

use Asmblah\HeapWalk\Result\Path\Descension\Root\RootInterface;
use Asmblah\HeapWalk\Result\Path\InstancePathSet;
use Asmblah\HeapWalk\Result\Path\PathSetInterface;
use Asmblah\HeapWalk\Walker\Visitor\InstancePathSetWalkVisitor;

/**
 * Class HeapWalker.
 *
 * Walks the heap starting from the given roots, using ValueWalker for recursion.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
class HeapWalker
{
    /**
     * @var RootInterface[]
     */
    private $roots;
    /**
     * @var ValueWalker
     */
    private $valueWalker;

    /**
     * @param ValueWalker $valueWalker
     * @param RootInterface[] $roots
     */
    public function __construct(ValueWalker $valueWalker, array $roots)
    {
        $this->roots = $roots;
        $this->valueWalker = $valueWalker;
    }

    /**
     * @param string[] $fqcns
     * @return PathSetInterface[]
     */
    public function getInstancePathSets(array $fqcns): array
    {
        $visitor = new InstancePathSetWalkVisitor($fqcns);

        foreach ($this->roots as $root) {
            $this->valueWalker->walkValue(
                $root->getValue(),
                [$visitor, 'visit'],
                [$root]
            );
        }

        $pathSets = [];

        foreach ($visitor->getDataByInstanceId() as $data) {
            $pathSets[] = new InstancePathSet($data['instance'], $data['paths']);
        }

        return $pathSets;
    }
}
