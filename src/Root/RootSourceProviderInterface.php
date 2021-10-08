<?php

declare(strict_types=1);

namespace Asmblah\HeapWalk\Root;

/**
 * Interface RootSourceProviderInterface.
 *
 * Fetches sources of roots from which to search for values. A helper for root providers.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
interface RootSourceProviderInterface
{
    /**
     * Fetches a backtrace to search for roots inside.
     *
     * @return array
     */
    public function getBacktrace(): array;

    /**
     * Fetches classes to search inside for roots.
     *
     * @return string[]
     */
    public function getClassNames(): array;

    /**
     * Fetches functions to search inside for roots.
     *
     * @return string[]
     */
    public function getFunctionNames(): array;

    /**
     * Fetches global variables to use as roots. Usually $GLOBALS.
     *
     * @return array
     */
    public function getGlobals(): array;
}
