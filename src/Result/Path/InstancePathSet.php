<?php

declare(strict_types=1);

namespace Asmblah\HeapWalk\Result\Path;

use Asmblah\HeapWalk\Result\ClassTools;
use InvalidArgumentException;

/**
 * Class InstancePathSet.
 *
 * Provides all paths from roots to the given object.
 * Note that different instances of the same class will each get a separate InstancePathSet.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
class InstancePathSet implements PathSetInterface
{
    /**
     * @var object
     */
    private $instance;
    /**
     * @var PathInterface[]
     */
    private $paths;

    /**
     * @param object $instance
     * @param PathInterface[] $paths
     */
    public function __construct(object $instance, array $paths)
    {
        $this->instance = $instance;
        $this->paths = $paths;
    }

    /**
     * @inheritDoc
     */
    public function diff(PathSetInterface $otherPathSet): PathSetInterface
    {
        if ($otherPathSet->getEventualValue() !== $this->instance) {
            throw new InvalidArgumentException(
                __METHOD__ . ' :: given path set is for a different eventual value'
            );
        }

        $pathsAsString = array_map(
            function (PathInterface $path) {
                return $path->toString();
            },
            $this->paths
        );

        $otherPathsAsString = array_map(
            function (PathInterface $path) {
                return $path->toString();
            },
            $otherPathSet->getPaths()
        );

        /** @var PathInterface[] $diffPaths */
        $diffPaths = [];

        foreach (array_keys(array_diff($pathsAsString, $otherPathsAsString)) as $index) {
            $diffPaths[] = $this->paths[$index];
        }

        return new InstancePathSet($this->instance, $diffPaths);
    }

    /**
     * @inheritDoc
     */
    public function getEventualValue()
    {
        return $this->instance;
    }

    /**
     * @inheritDoc
     */
    public function getPaths(): array
    {
        return $this->paths;
    }

    /**
     * @inheritDoc
     */
    public function toArray(): array
    {
        return [
            'class' => ClassTools::toReadableClassName(get_class($this->instance)),
            'paths' => array_map(
                function (PathInterface $path): string {
                    return $path->toString();
                },
                $this->paths
            )
        ];
    }
}
