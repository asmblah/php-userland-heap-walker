<?php

declare(strict_types=1);

namespace Asmblah\HeapWalk\Walker;

use Asmblah\HeapWalk\Result\Path\Descension\Root\RootInterface;
use Asmblah\HeapWalk\Result\Path\InstancePathSet;
use Asmblah\HeapWalk\Result\Path\Path;
use Asmblah\HeapWalk\Result\Path\PathSetInterface;

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
        /** @var array<int, array{instance: object, paths: Path}> $dataByInstanceId */
        $dataByInstanceId = [];
        /** @var array<int, bool> $instanceIdsVisited */
        $instanceIdsVisited = [];

        foreach ($this->roots as $root) {
            $this->valueWalker->walkValue(
                $root->getValue(),
                function ($value, array $descensions) use ($fqcns, &$dataByInstanceId, &$instanceIdsVisited) {
                    if (!is_object($value)) {
                        // We're only interested in inspecting objects, but we still want to recurse
                        // as applicable, e.g. into arrays.
                        return true;
                    }

                    $descend = true;
                    $objectId = spl_object_id($value);

                    if (array_key_exists($objectId, $instanceIdsVisited)) {
                        // We already processed this object, don't descend into it again.
                        $descend = false;
                    } else {
                        $instanceIdsVisited[$objectId] = true;
                    }

                    // TODO: Handle subclasses.
                    if (in_array(get_class($value), $fqcns, true)) {
                        $path = new Path($descensions);

                        if (!array_key_exists($objectId, $dataByInstanceId)) {
                            $dataByInstanceId[$objectId] = ['instance' => $value, 'paths' => []];
                        }

                        $dataByInstanceId[$objectId]['paths'][] = $path;
                    }

                    return $descend;
                },
                [$root]
            );
        }

        $pathSets = [];

        foreach ($dataByInstanceId as $data) {
            $pathSets[] = new InstancePathSet($data['instance'], $data['paths']);
        }

        return $pathSets;
    }
}
