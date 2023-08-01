<?php

declare(strict_types=1);

namespace Asmblah\HeapWalk\Walker\Visitor;

use Asmblah\HeapWalk\Result\Path\Path;
use Asmblah\HeapWalk\Result\Path\PathInterface;

/**
 * Class InstancePathSetWalkVisitor.
 *
 * Handles visiting instances discovered during instance path set heap walks.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
class InstancePathSetWalkVisitor
{
    /**
     * @var array<int, array{instance: object, paths: PathInterface[]}>
     */
    private $dataByInstanceId = [];
    /**
     * @var string[]
     */
    private $fqcns;
    /**
     * @var array<int, bool>
     */
    private $instanceIdsVisited = [];

    /**
     * @param string[] $fqcns
     */
    public function __construct(array $fqcns)
    {
        $this->fqcns = $fqcns;
    }

    /**
     * @return array<int, array{instance: object, paths: PathInterface[]}>
     */
    public function getDataByInstanceId(): array
    {
        return $this->dataByInstanceId;
    }

    /**
     * Visits the given value and determines whether to descend into it.
     *
     * @param mixed $value
     * @param array $descensions
     * @return bool
     */
    public function visit($value, array $descensions): bool
    {
        if (!is_object($value)) {
            // We're only interested in inspecting objects, but we still want to recurse
            // as applicable, e.g. into arrays.
            return true;
        }

        $descend = true;
        $objectId = spl_object_id($value);

        if (array_key_exists($objectId, $this->instanceIdsVisited)) {
            // We already processed this object, don't descend into it again.
            $descend = false;
        } else {
            $this->instanceIdsVisited[$objectId] = true;
        }

        // TODO: Handle subclasses.
        if (in_array(get_class($value), $this->fqcns, true)) {
            $path = new Path($descensions);

            if (!array_key_exists($objectId, $this->dataByInstanceId)) {
                $this->dataByInstanceId[$objectId] = ['instance' => $value, 'paths' => []];
            }

            $this->dataByInstanceId[$objectId]['paths'][] = $path;
        }

        return $descend;
    }
}
