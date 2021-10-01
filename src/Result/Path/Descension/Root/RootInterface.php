<?php

declare(strict_types=1);

namespace Asmblah\HeapWalk\Result\Path\Descension\Root;

use Asmblah\HeapWalk\Result\Path\Descension\DescensionInterface;

/**
 * Interface RootInterface.
 *
 * Roots are values to start scanning recursively from. They should be loosely equivalent
 * to the roots gathered by the garbage collector, except for the caveats noted in README.md.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
interface RootInterface extends DescensionInterface
{
}
