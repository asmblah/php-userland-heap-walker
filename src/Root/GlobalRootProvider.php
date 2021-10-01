<?php

declare(strict_types=1);

namespace Asmblah\HeapWalk\Root;

use Asmblah\HeapWalk\Result\Path\Descension\Root\GlobalVariableRoot;
use Asmblah\HeapWalk\Result\Path\Descension\Root\RootInterface;
use Asmblah\HeapWalk\Result\Path\Descension\Root\StaticPropertyRoot;
use Asmblah\HeapWalk\Result\Path\Descension\Root\StaticVariableRoot;
use ReflectionClass;
use ReflectionException;
use ReflectionFunction;

/**
 * Class GlobalRootProvider.
 *
 * Discovers all accessible roots from which to search for values.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
class GlobalRootProvider
{
    /**
     * @var array
     */
    private $classNames;
    /**
     * @var string[]
     */
    private $functionNames;
    /**
     * @var array
     */
    private $globals;

    /**
     * @param array $globals Global variables to check. Usually passed $GLOBALS[].
     * @param string[] $functionNames Fully qualified functions to check the static variables of.
     * @param string[] array $classNames Fully qualified functions to check the static properties of.
     */
    public function __construct(array $globals, array $functionNames, array $classNames)
    {
        $this->classNames = $classNames;
        $this->functionNames = $functionNames;
        $this->globals = $globals;
    }

    /**
     * Fetches all root values to check recursively in order to walk the entire userland heap.
     * This should be mostly equivalent to the GC roots.
     *
     * @return RootInterface[]
     * @throws ReflectionException
     */
    public function getRoots(): array
    {
        $rootValues = [];

        foreach ($this->globals as $name => $value) {
            $rootValues[] = new GlobalVariableRoot($name, $value);
        }

        foreach ($this->functionNames as $functionName) {
            $reflectionFunction = new ReflectionFunction($functionName);

            foreach ($reflectionFunction->getStaticVariables() as $variableName => $value) {
                $rootValues[] = new StaticVariableRoot($functionName, $variableName, $value);
            }
        }

        foreach ($this->classNames as $className) {
            $reflectionClass = new ReflectionClass($className);

            foreach ($reflectionClass->getStaticProperties() as $propertyName => $value) {
                $rootValues[] = new StaticPropertyRoot($className, $propertyName, $value);
            }
        }

        // TODO: Capture arguments from debug_backtrace()?

        return $rootValues;
    }
}
