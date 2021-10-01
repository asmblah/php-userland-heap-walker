<?php

declare(strict_types=1);

namespace Asmblah\HeapWalk\Result;

/**
 * Interface ResultInterface.
 *
 * A common base interface for all heap walk result information.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
interface ResultInterface
{
    /**
     * @return array
     */
    public function toArray(): array;
}
