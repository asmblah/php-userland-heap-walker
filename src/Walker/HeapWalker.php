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

        foreach ($this->roots as $root) {
            $this->valueWalker->walkValue(
                $root->getValue(),
                function ($value, array $descensions) use ($fqcns, &$dataByInstanceHash) {
                    if (is_object($value) && in_array(get_class($value), $fqcns, true)) {
                        $hash = spl_object_hash($value);

                        if (!array_key_exists($hash, $dataByInstanceHash)) {
                            $dataByInstanceHash[$hash] = ['instance' => $value, 'paths' => []];
                        }

                        $dataByInstanceHash[$hash]['paths'][] = new Path($descensions);
                    }
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
