<?php

declare(strict_types=1);

namespace Asmblah\HeapWalk\Tests\Acceptance\Functional\Walk;

use Asmblah\HeapWalk\HeapWalk;
use Asmblah\HeapWalk\Tests\Acceptance\AcceptanceTestCase;
use Asmblah\HeapWalk\Tests\Acceptance\Fixtures\Classes\Thing;

/**
 * Class DiffingResultsFunctionalTest.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
class DiffingResultsFunctionalTest extends AcceptanceTestCase
{
    /**
     * @var HeapWalk
     */
    private $heapWalk;
    /**
     * @var Thing[]
     */
    private $things;

    public function setUp(): void
    {
        $this->things = [new Thing('my first thing')];

        $this->heapWalk = new HeapWalk();
    }

    public function tearDown(): void
    {
        // Clear down data so that it does not affect subsequent tests,
        // as PHPUnit keeps test instances around until the end of the run.
        $this->heapWalk = null;
        $this->things = [];
    }

    public function testDiffingResultsGivesEmptyDiffWhenReachableHeapIsUnchanged(): void
    {
        $initialCollection = $this->heapWalk->getInstancePathSetCollection([Thing::class]);
        $laterCollection = $this->heapWalk->getInstancePathSetCollection([Thing::class]);

        $diff = $laterCollection->diff($initialCollection);

        static::assertCount(0, $diff->getPathSets());
    }

    public function testDiffingResultsGivesCorrectDiffWhenReachableHeapHasChanged(): void
    {
        $initialCollection = $this->heapWalk->getInstancePathSetCollection([Thing::class]);
        $this->things[] = new Thing('my second thing');
        $laterCollection = $this->heapWalk->getInstancePathSetCollection([Thing::class]);

        $diff = $laterCollection->diff($initialCollection);

        static::assertCount(1, $diff->getPathSets());
        static::assertSame($this->things[1], $diff->getPathSets()[0]->getEventualValue());
        static::assertCount(1, $diff->getPathSets()[0]->getPaths());
    }
}
