<?php

declare(strict_types=1);

namespace Asmblah\HeapWalk\Root;

use ReflectionClass;

/**
 * Class RootSourceProvider.
 *
 * Fetches sources of roots from which to search for values. A helper for root providers.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
class RootSourceProvider implements RootSourceProviderInterface
{
    /**
     * @inheritDoc
     */
    public function getBacktrace(): array
    {
        return debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT);
    }

    /**
     * @inheritDoc
     */
    public function getClassNames(): array
    {
        // Fetch all standard declared classes.
        $classNames = array_merge(get_declared_classes(), get_declared_traits());

        // Add any anonymous classes in the scope of a stack frame.
        foreach ($this->getBacktrace() as $stackFrame) {
            if (!array_key_exists('class', $stackFrame)) {
                continue;
            }

            $className = $stackFrame['class'];

            $reflectionClass = new ReflectionClass($className);

            if (!$reflectionClass->isAnonymous()) {
                continue;
            }

            $classNames[] = $className;
        }

        return array_unique($classNames);
    }

    /**
     * @inheritDoc
     */
    public function getFunctionNames(): array
    {
        // Note that internal functions are ignored.
        /** @noinspection PotentialMalwareInspection */
        return get_defined_functions()['user'];
    }

    /**
     * @inheritDoc
     */
    public function getGlobals(): array
    {
        return $GLOBALS;
    }
}
