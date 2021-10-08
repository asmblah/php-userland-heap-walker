<?php

declare(strict_types=1);

namespace Asmblah\HeapWalk\Root;

use Asmblah\HeapWalk\Result\ClassTools;
use Asmblah\HeapWalk\Result\Path\Descension\Root\ParameterArgumentRoot;

/**
 * Class BacktraceRootProvider.
 *
 * Discovers accessible roots from which to search for values from a backtrace.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
class BacktraceRootProvider implements RootProviderInterface
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

        foreach ($this->rootSourceProvider->getBacktrace() as $stackFrame) {
            $function = array_key_exists('class', $stackFrame) ?
                ClassTools::toReadableClassName($stackFrame['class']) . $stackFrame['type'] . $stackFrame['function'] :
                $stackFrame['function'];

            // Add the call's arguments as roots.
            foreach ($stackFrame['args'] as $argument) {
                $rootValues[] = new ParameterArgumentRoot($function, $argument);
            }
        }

        return $rootValues;
    }
}
