<?php

declare(strict_types=1);

namespace Asmblah\HeapWalk\Tests\Unit\Result\Path;

use Asmblah\HeapWalk\Result\Path\PathInterface;
use Asmblah\HeapWalk\Result\Path\PathSetCollection;
use Asmblah\HeapWalk\Result\Path\PathSetInterface;
use Asmblah\HeapWalk\Tests\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;

/**
 * Class PathSetCollectionTest.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
class PathSetCollectionTest extends TestCase
{
    use ProphecyTrait;

    /**
     * @var ObjectProphecy
     */
    private $instance1;
    /**
     * @var ObjectProphecy
     */
    private $instance2;
    /**
     * @var ObjectProphecy
     */
    private $instance3;
    /**
     * @var ObjectProphecy&PathSetInterface
     */
    private $pathSet1;
    /**
     * @var ObjectProphecy&PathInterface
     */
    private $pathSet1Path1;
    /**
     * @var ObjectProphecy&PathInterface
     */
    private $pathSet1Path2;
    /**
     * @var ObjectProphecy&PathSetInterface
     */
    private $pathSet2;
    /**
     * @var ObjectProphecy&PathInterface
     */
    private $pathSet2Path1;
    /**
     * @var ObjectProphecy&PathInterface
     */
    private $pathSet2Path2;
    /**
     * @var ObjectProphecy&PathSetInterface
     */
    private $pathSet3;
    /**
     * @var ObjectProphecy&PathInterface
     */
    private $pathSet3Path1;
    /**
     * @var ObjectProphecy&PathInterface
     */
    private $pathSet3Path2;
    /**
     * @var PathSetCollection
     */
    private $pathSetCollection;

    public function setUp(): void
    {
        $this->instance1 = $this->prophesize();
        $this->instance2 = $this->prophesize();
        $this->instance3 = $this->prophesize();
        $this->pathSet1 = $this->prophesize(PathSetInterface::class);
        $this->pathSet1Path1 = $this->prophesize(PathInterface::class);
        $this->pathSet1Path2 = $this->prophesize(PathInterface::class);
        $this->pathSet2 = $this->prophesize(PathSetInterface::class);
        $this->pathSet2Path1 = $this->prophesize(PathInterface::class);
        $this->pathSet2Path2 = $this->prophesize(PathInterface::class);
        $this->pathSet3 = $this->prophesize(PathSetInterface::class);
        $this->pathSet3Path1 = $this->prophesize(PathInterface::class);
        $this->pathSet3Path2 = $this->prophesize(PathInterface::class);

        $this->pathSet1->getEventualValue()->willReturn($this->instance1);
        $this->pathSet1->getPaths()->willReturn([
            $this->pathSet1Path1->reveal(),
            $this->pathSet1Path2->reveal(),
        ]);
        $this->pathSet1->toArray()->willReturn([
            'class' => 'My\FirstClass',
            'paths' => ['set 1 path 1', 'set 1 path 2'],
        ]);
        $this->pathSet2->getEventualValue()->willReturn($this->instance2);
        $this->pathSet2->getPaths()->willReturn([
            $this->pathSet2Path1->reveal(),
            $this->pathSet2Path2->reveal(),
        ]);
        $this->pathSet2->toArray()->willReturn([
            'class' => 'My\SecondClass',
            'paths' => ['set 2 path 1', 'set 2 path 2'],
        ]);
        $this->pathSet3->getEventualValue()->willReturn($this->instance3);
        $this->pathSet3->getPaths()->willReturn([
            $this->pathSet3Path1->reveal(),
            $this->pathSet3Path2->reveal(),
        ]);
        $this->pathSet3->toArray()->willReturn([
            'class' => 'My\ThirdClass',
            'paths' => ['set 3 path 1', 'set 3 path 2'],
        ]);

        $this->pathSetCollection = new PathSetCollection(
            [$this->pathSet1->reveal(), $this->pathSet2->reveal(), $this->pathSet3->reveal()]
        );
    }

    public function testDiffReturnsOnlySetsWithOnlyDifferentPaths(): void
    {
        $otherInstance = $this->prophesize();
        $otherPathSet1 = $this->prophesize(PathSetInterface::class);
        $diffPathSet1 = $this->prophesize(PathSetInterface::class);
        $otherPathSet2 = $this->prophesize(PathSetInterface::class);
        $diffPathSet2 = $this->prophesize(PathSetInterface::class);
        $diffPathSet2Path1 = $this->prophesize(PathInterface::class);
        $otherPathSet3 = $this->prophesize(PathSetInterface::class);

        $otherPathSet1->getEventualValue()->willReturn($this->instance1);
        $this->pathSet1->diff($otherPathSet1)->willReturn($diffPathSet1);
        $diffPathSet1->getPaths()->willReturn([]);

        $otherPathSet2->getEventualValue()->willReturn($this->instance2);
        $this->pathSet2->diff($otherPathSet2)->willReturn($diffPathSet2);
        $diffPathSet2->getPaths()->willReturn([
            $diffPathSet2Path1->reveal(),
        ]);
        $diffPathSet2Path1->getEventualValue()->willReturn($this->instance2);
        $otherPathSet3->getEventualValue()->willReturn($otherInstance);

        $otherPathSetCollection = new PathSetCollection(
            [$otherPathSet1->reveal(), $otherPathSet2->reveal(), $otherPathSet3->reveal()]
        );

        $result = $this->pathSetCollection->diff($otherPathSetCollection);

        static::assertCount(2, $result->getPathSets());
        static::assertCount(1, $result->getPathSets()[0]->getPaths());
        static::assertSame(
            $this->instance2->reveal(),
            $result->getPathSets()[0]->getPaths()[0]->getEventualValue()
        );
        // Third original path set should be included because its eventual value
        // does not appear in the second collection at all.
        static::assertCount(2, $result->getPathSets()[1]->getPaths());
        static::assertSame($this->pathSet3Path1->reveal(), $result->getPathSets()[1]->getPaths()[0]);
        static::assertSame($this->pathSet3Path2->reveal(), $result->getPathSets()[1]->getPaths()[1]);
    }

    public function testToArray(): void
    {
        $result = $this->pathSetCollection->toArray();

        static::assertEquals(
            [
                'path_sets' => [
                    [
                        'class' => 'My\FirstClass',
                        'paths' => ['set 1 path 1', 'set 1 path 2'],
                    ],
                    [
                        'class' => 'My\SecondClass',
                        'paths' => ['set 2 path 1', 'set 2 path 2'],
                    ],
                    [
                        'class' => 'My\ThirdClass',
                        'paths' => ['set 3 path 1', 'set 3 path 2'],
                    ]
                ],
            ],
            $result
        );
    }
}
