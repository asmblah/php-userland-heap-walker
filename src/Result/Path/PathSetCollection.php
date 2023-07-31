<?php

declare(strict_types=1);

namespace Asmblah\HeapWalk\Result\Path;

/**
 * Class PathSetCollection.
 *
 * Path set collections group all path sets discovered as a result of a heap walk.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
class PathSetCollection implements PathSetCollectionInterface
{
    /**
     * @var PathSetInterface[]
     */
    private $pathSets;

    /**
     * @param PathSetInterface[] $pathSets
     */
    public function __construct(array $pathSets)
    {
        $this->pathSets = $pathSets;
    }

    /**
     * @inheritDoc
     */
    public function diff(PathSetCollectionInterface $otherPathSetCollection): PathSetCollectionInterface
    {
        /** @var array<int, object> $instancesById */
        $instancesById = [];
        /** @var array<int, PathInterface[]> $pathSetsByInstanceId */
        $pathSetsByInstanceId = [];

        foreach ($this->pathSets as $pathSet) {
            $instance = $pathSet->getEventualValue();
            $instanceId = spl_object_id($instance);

            $instancesById[$instanceId] = $instance;

            foreach ($otherPathSetCollection->getPathSets() as $otherPathSet) {
                if ($otherPathSet->getEventualValue() === $instance) {
                    // This path set is for the same instance.

                    $diffPathSet = $pathSet->diff($otherPathSet);

                    if (count($diffPathSet->getPaths()) > 0) {
                        $pathSetsByInstanceId[$instanceId] = $diffPathSet->getPaths();
                    }

                    continue 2;
                }
            }

            $pathSetsByInstanceId[$instanceId] = $pathSet->getPaths();
        }

        /** @var PathSetInterface[] $pathSets */
        $pathSets = [];

        foreach ($pathSetsByInstanceId as $instanceId => $paths) {
            if (count($paths) > 0) {
                $pathSets[] = new InstancePathSet($instancesById[$instanceId], $paths);
            }
        }

        return new PathSetCollection($pathSets);
    }

    /**
     * @inheritDoc
     */
    public function getPathSets(): array
    {
        return $this->pathSets;
    }

    /**
     * @inheritDoc
     */
    public function toArray(): array
    {
        return [
            'path_sets' => array_map(
                function (PathSetInterface $pathSet): array {
                    return $pathSet->toArray();
                },
                $this->pathSets
            )
        ];
    }
}
