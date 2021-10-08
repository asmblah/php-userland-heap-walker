<?php

declare(strict_types=1);

namespace Asmblah\HeapWalk\Root;

/**
 * Class DelegatingRootProviderInterface.
 *
 * Discovers accessible roots from which to search for values from a set of registered providers.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
interface DelegatingRootProviderInterface extends RootProviderInterface
{
    /**
     * Registers a new root provider.
     *
     * @param RootProviderInterface $rootProvider
     */
    public function registerProvider(RootProviderInterface $rootProvider): void;
}
