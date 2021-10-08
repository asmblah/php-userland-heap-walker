<?php

declare(strict_types=1);

namespace Asmblah\HeapWalk\Root;

use Asmblah\HeapWalk\Result\Path\Descension\Root\GlobalVariableRoot;

/**
 * Class GlobalVariableRootProvider.
 *
 * Discovers accessible roots from which to search for values from global variables.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
class GlobalVariableRootProvider implements RootProviderInterface
{
    /**
     * @var RootSourceProviderInterface
     */
    private $rootSourceProvider;

    /**
     * @param RootSourceProviderInterface $rootSourceProvider
     */
    public function __construct(RootSourceProviderInterface $rootSourceProvider)
    {
        $this->rootSourceProvider = $rootSourceProvider;
    }

    /**
     * @inheritDoc
     */
    public function getRoots(): array
    {
        $rootValues = [];

        foreach ($this->rootSourceProvider->getGlobals() as $name => $value) {
            $rootValues[] = new GlobalVariableRoot($name, $value);
        }

        return $rootValues;
    }
}
