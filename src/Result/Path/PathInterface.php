<?php

declare(strict_types=1);

namespace Asmblah\HeapWalk\Result\Path;

use Asmblah\HeapWalk\Result\Path\Descension\DescensionInterface;
use Asmblah\HeapWalk\Result\ResultInterface;

/**
 * Interface PathInterface.
 *
 * Paths group a series of successive descensions together to form a path from a root to an eventual value.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
interface PathInterface extends ResultInterface
{
    /**
     * @return DescensionInterface[]
     */
    public function getDescensions(): array;

    /**
     * @return mixed
     */
    public function getEventualValue();

    /**
     * @return string
     */
    public function toString(): string;
}
