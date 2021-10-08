<?php

declare(strict_types=1);

namespace Asmblah\HeapWalk\Root;

/**
 * Interface RootProviderFactoryInterface.
 *
 * Creates a DelegatingRootProvider with relevant root providers registered.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
interface RootProviderFactoryInterface
{
    /**
     * Creates a DelegatingRootProvider.
     *
     * @return DelegatingRootProviderInterface
     */
    public function createProvider(): DelegatingRootProviderInterface;
}
