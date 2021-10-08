<?php

declare(strict_types=1);

namespace Asmblah\HeapWalk\Tests\Acceptance\Integrated\Root;

use Asmblah\HeapWalk\Root\DelegatingRootProvider;
use Asmblah\HeapWalk\Root\RootProviderFactory;
use Asmblah\HeapWalk\Root\RootSourceProviderInterface;
use Asmblah\HeapWalk\Tests\Acceptance\AcceptanceTestCase;
use Asmblah\HeapWalk\Tests\Acceptance\Fixtures\Classes\Thing;
use Asmblah\HeapWalk\Tests\Acceptance\Fixtures\Classes\WithStaticProperties;
use Asmblah\HeapWalk\Tests\Acceptance\Fixtures\Classes\WithStaticVariablesInsideMethods;
use Prophecy\PhpUnit\ProphecyTrait;
use function Asmblah\HeapWalk\Tests\Acceptance\Fixtures\Functions\with_two_static_variables;

/**
 * Class RootProviderIntegratedTest.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
class RootProviderIntegratedTest extends AcceptanceTestCase
{
    use ProphecyTrait;

    /**
     * @var Thing
     */
    private $firstGlobal;
    /**
     * @var Thing
     */
    private $globalFunctionArg;
    /**
     * @var Thing
     */
    private $instanceMethodArg;
    /**
     * @var DelegatingRootProvider
     */
    private $rootProvider;
    /**
     * @var Thing
     */
    private $secondGlobal;
    /**
     * @var Thing
     */
    private $staticMethodArg;
    /**
     * @var Thing
     */
    private $staticPropertyValue;
    /**
     * @var Thing
     */
    private $staticVariableValue;

    public function setUp(): void
    {
        $this->firstGlobal = new Thing('my first one');
        $this->secondGlobal = new Thing('my second one');

        $this->staticVariableValue = new Thing('my static variable instance');
        with_two_static_variables($this->staticVariableValue);

        $this->staticPropertyValue = new Thing('my static property instance');
        WithStaticProperties::setMyInstance($this->staticPropertyValue);

        $this->globalFunctionArg = new Thing('an argument to global function');
        $this->instanceMethodArg = new Thing('an argument to instance method');
        $this->staticMethodArg = new Thing('an argument to static method');

        $rootSourceProvider = $this->prophesize(RootSourceProviderInterface::class);
        $rootSourceProvider->getBacktrace()
            ->willReturn([
                [
                    'function' => 'my_func',
                    'args' => [],
                ],
                [
                    'function' => 'my_func',
                    'args' => [$this->globalFunctionArg],
                ],
                [
                    'class' => 'My\\Stuff\\MyClass',
                    'type' => '::',
                    'function' => 'myStaticMethod',
                    'args' => [$this->staticMethodArg],
                ],
                [
                    'class' => 'My\\Stuff\\MyClass',
                    'type' => '->',
                    'function' => 'myInstanceMethod',
                    'args' => [$this->instanceMethodArg],
                ],
            ]);
        $rootSourceProvider->getClassNames()
            ->willReturn([
                WithStaticProperties::class,
                WithStaticVariablesInsideMethods::class
            ]);
        $rootSourceProvider->getFunctionNames()
            ->willReturn([
                'Asmblah\HeapWalk\Tests\Acceptance\Fixtures\Functions\with_no_static_variables',
                'Asmblah\HeapWalk\Tests\Acceptance\Fixtures\Functions\with_two_static_variables',
            ]);
        $rootSourceProvider->getGlobals()
            ->willReturn([
                'firstGlobal' => $this->firstGlobal,
                'secondGlobal' => $this->secondGlobal,
            ]);

        // Create a DelegatingRootProvider using the standard RootProviderFactory,
        // so that we register all the built-in providers.
        $this->rootProvider = (new RootProviderFactory($rootSourceProvider->reveal()))->createProvider();
    }

    public function testGetRootsReturnsCorrectRoots(): void
    {
        $rootValues = $this->rootProvider->getRoots();

        static::assertCount(11, $rootValues);
        static::assertEquals(
            [
                'function' => 'my_func',
                'value' => $this->globalFunctionArg,
            ],
            $rootValues[0]->toArray()
        );
        static::assertEquals(
            [
                'function' => 'My\\Stuff\\MyClass::myStaticMethod',
                'value' => $this->staticMethodArg,
            ],
            $rootValues[1]->toArray()
        );
        static::assertEquals(
            [
                'function' => 'My\\Stuff\\MyClass->myInstanceMethod',
                'value' => $this->instanceMethodArg,
            ],
            $rootValues[2]->toArray()
        );
        static::assertEquals(
            [
                'class' => WithStaticProperties::class,
                'property' => 'myInstance',
                'value' => $this->staticPropertyValue,
            ],
            $rootValues[3]->toArray()
        );
        static::assertEquals(
            [
                'class' => WithStaticProperties::class,
                'property' => 'aString',
                'value' => 'my string in a static property',
            ],
            $rootValues[4]->toArray()
        );
        static::assertEquals(
            [
                'value' => 'my value for static var in instance method',
                'function' => WithStaticVariablesInsideMethods::class . '::myInstanceMethod',
                'name' => 'myVarInsideInstanceMethod',
            ],
            $rootValues[5]->toArray()
        );
        static::assertEquals(
            [
                'value' => 'my value for static var in static method',
                'function' => WithStaticVariablesInsideMethods::class . '::myStaticMethod',
                'name' => 'myVarInsideStaticMethod',
            ],
            $rootValues[6]->toArray()
        );
        static::assertEquals(
            [
                'function' => 'Asmblah\HeapWalk\Tests\Acceptance\Fixtures\Functions\with_two_static_variables',
                'name' => 'aString',
                'value' => 'my string in a static variable',
            ],
            $rootValues[7]->toArray()
        );
        static::assertEquals(
            [
                'function' => 'Asmblah\HeapWalk\Tests\Acceptance\Fixtures\Functions\with_two_static_variables',
                'name' => 'myInstance',
                'value' => $this->staticVariableValue,
            ],
            $rootValues[8]->toArray()
        );
        static::assertEquals(
            [
                'name' => 'firstGlobal',
                'value' => $this->firstGlobal,
            ],
            $rootValues[9]->toArray()
        );
        static::assertEquals(
            [
                'name' => 'secondGlobal',
                'value' => $this->secondGlobal,
            ],
            $rootValues[10]->toArray()
        );
    }
}
