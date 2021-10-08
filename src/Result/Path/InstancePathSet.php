<?php

declare(strict_types=1);

namespace Asmblah\HeapWalk\Result\Path;

use Asmblah\HeapWalk\Result\ClassTools;

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
