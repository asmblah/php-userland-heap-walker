<?php

declare(strict_types=1);

namespace Asmblah\HeapWalk\Root;

/**
 * Class RootProviderFactory.
 *
 * Creates a DelegatingRootProvider with the built-in root providers registered.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
class RootProviderFactory implements RootProviderFactoryInterface
{
    /**
     * @var RootSourceProviderInterface
     */
    private $rootSourceProvider;

    /**
     * @param RootSourceProviderInterface|null $rootSourceProvider
     */
    public function __construct(?RootSourceProviderInterface $rootSourceProvider = null)
    {
        $this->rootSourceProvider = $rootSourceProvider ?? new RootSourceProvider();
    }

    /**
     * @inheritDoc
     */
    public function createProvider(): DelegatingRootProviderInterface
    {
        $delegatingRootProvider = new DelegatingRootProvider();

        // Install the built-in providers.
        $delegatingRootProvider->registerProvider(new BacktraceRootProvider($this->rootSourceProvider));
        $delegatingRootProvider->registerProvider(new ClassDescendantRootProvider($this->rootSourceProvider));
        $delegatingRootProvider->registerProvider(new FunctionStaticVariableRootProvider($this->rootSourceProvider));
        $delegatingRootProvider->registerProvider(new GlobalVariableRootProvider($this->rootSourceProvider));

        return $delegatingRootProvider;
    }
}
