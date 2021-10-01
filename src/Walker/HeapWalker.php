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
        $dataByInstanceHash = [];
        $instanceHashesVisited = [];

        foreach ($this->roots as $root) {
            $this->valueWalker->walkValue(
                $root->getValue(),
                function ($value, array $descensions) use ($fqcns, &$dataByInstanceHash, &$instanceHashesVisited) {
                    if (!is_object($value)) {
                        // We're only interested in inspecting objects, but we still want to recurse
                        // as applicable, eg. into arrays.
                        return true;
                    }

                    $descend = true;
                    $hash = spl_object_hash($value);

                    if (array_key_exists($hash, $instanceHashesVisited)) {
                        // We already processed this object, don't descend into it again.
                        $descend = false;
                    } else {
                        $instanceHashesVisited[$hash] = true;
                    }

                    // TODO: Handle subclasses
                    if (in_array(get_class($value), $fqcns, true)) {
                        $hash = spl_object_hash($value);
                        $path = new Path($descensions);

                        if (!array_key_exists($hash, $dataByInstanceHash)) {
                            $dataByInstanceHash[$hash] = ['instance' => $value, 'paths' => []];
                        }

                        $dataByInstanceHash[$hash]['paths'][] = $path;
                    }

                    return $descend;
                },
                [$root]
            );
        }

        $pathSets = [];

        foreach ($dataByInstanceHash as $data) {
            $pathSets[] = new InstancePathSet($data['instance'], $data['paths']);
        }

        return $pathSets;
    }
}
