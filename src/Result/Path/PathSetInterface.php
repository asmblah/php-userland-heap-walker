<?php

declare(strict_types=1);

namespace Asmblah\HeapWalk\Result\Path;

use Asmblah\HeapWalk\Result\ResultInterface;

/**
 * Interface PathSetInterface.
 *
 * Path sets group all discovered paths from roots to a value.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
interface PathSetInterface extends ResultInterface
{
    /**
     * @return mixed
     */
    public function getEventualValue();

    /**
     * @return PathInterface[]
     */
    public function getPaths(): array;
}
