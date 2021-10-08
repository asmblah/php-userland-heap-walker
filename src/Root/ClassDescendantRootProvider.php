<?php

declare(strict_types=1);

namespace Asmblah\HeapWalk\Root;

use Asmblah\HeapWalk\Result\Path\Descension\Root\StaticPropertyRoot;
use Asmblah\HeapWalk\Result\Path\Descension\Root\StaticVariableRoot;
use ReflectionClass;

/**
 * Class ClassDescendantRootProvider.
 *
 * Discovers accessible roots from which to search for values from classes.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
class ClassDescendantRootProvider implements RootProviderInterface
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

        foreach ($this->rootSourceProvider->getClassNames() as $className) {
            $reflectionClass = new ReflectionClass($className);

            // Capture static property values for classes (both default and dynamically-set).
            foreach ($reflectionClass->getStaticProperties() as $propertyName => $value) {
                $rootValues[] = new StaticPropertyRoot($className, $propertyName, $value);
            }

            // Capture static variables' values declared inside both static & instance methods.
            foreach ($reflectionClass->getMethods() as $reflectionMethod) {
                $methodName = $reflectionMethod->getName();

                foreach ($reflectionMethod->getStaticVariables() as $variableName => $value) {
                    $rootValues[] = new StaticVariableRoot($className . '::' . $methodName, $variableName, $value);
                }
            }
        }

        return $rootValues;
    }
}
