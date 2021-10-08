<?php

declare(strict_types=1);

namespace Asmblah\HeapWalk\Root;

/**
 * Class DelegatingRootProvider.
 *
 * Discovers accessible roots from which to search for values from a set of registered providers.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
class DelegatingRootProvider implements DelegatingRootProviderInterface
{
    /**
     * @var RootProviderInterface[]
     */
    private $rootProviders = [];

    /**
     * @inheritDoc
     */
    public function registerProvider(RootProviderInterface $rootProvider): void
    {
        $this->rootProviders[] = $rootProvider;
    }

    /**
     * @inheritDoc
     */
    public function getRoots(): array
    {
        $rootValues = [];

        foreach ($this->rootProviders as $rootProvider) {
            $rootValues = array_merge($rootValues, $rootProvider->getRoots());
        }

        return $rootValues;
    }
}
