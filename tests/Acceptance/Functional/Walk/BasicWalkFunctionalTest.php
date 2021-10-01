<?php

declare(strict_types=1);

namespace Asmblah\HeapWalk\Tests\Acceptance\Functional\Walk;

use Asmblah\HeapWalk\HeapWalk;
use Asmblah\HeapWalk\Tests\Acceptance\AcceptanceTestCase;
use Asmblah\HeapWalk\Tests\Acceptance\Fixtures\Classes\Thing;

/**
 * Class BasicWalkFunctionalTest.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
class BasicWalkFunctionalTest extends AcceptanceTestCase
{
    /**
     * @var HeapWalk
     */
    private $heapWalk;
    /**
     * @var Thing
     */
    private static $thing;

    public function setUp(): void
    {
        self::$thing = new Thing('a property to find of this PHPUnit test instance');

        $this->heapWalk = new HeapWalk();
    }

    protected function tearDown(): void
    {
        self::$thing = null;
    }

    public function testGetInstancePathSetsFetchesRealHeapValues(): void
    {
        $pathSets = $this->heapWalk->getInstancePathSets([Thing::class]);

        static::assertCount(1, $pathSets);
        static::assertEquals(
            [
                'fqcn' => Thing::class,
                'paths' => [
                    __CLASS__ . '::$thing',
                ],
            ],
            $pathSets[0]->toArray()
        );
    }
}
