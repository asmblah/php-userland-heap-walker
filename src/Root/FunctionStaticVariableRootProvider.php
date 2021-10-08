<?php

declare(strict_types=1);

namespace Asmblah\HeapWalk\Root;

use Asmblah\HeapWalk\Result\Path\Descension\Root\StaticVariableRoot;
use ReflectionFunction;

/**
 * Class FunctionStaticVariableRootProvider.
 *
 * Discovers accessible roots from which to search for values from global functions.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
class FunctionStaticVariableRootProvider implements RootProviderInterface
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

        // Capture static variables' values declared inside global functions.
        foreach ($this->rootSourceProvider->getFunctionNames() as $functionName) {
            $reflectionFunction = new ReflectionFunction($functionName);

            foreach ($reflectionFunction->getStaticVariables() as $variableName => $value) {
                $rootValues[] = new StaticVariableRoot($functionName, $variableName, $value);
            }
        }

        return $rootValues;
    }
}
