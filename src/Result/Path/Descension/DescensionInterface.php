<?php

declare(strict_types=1);

namespace Asmblah\HeapWalk\Result\Path\Descension;

use Asmblah\HeapWalk\Result\ResultInterface;

/**
 * Interface DescensionInterface.
 *
 * Descensions represent references from a parent value to a child.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
interface DescensionInterface extends ResultInterface
{
    /**
     * @return string
     */
    public function getName(): string;

    /**
     * @return mixed
     */
    public function getValue();

    /**
     * @return string
     */
    public function toString(): string;
}
