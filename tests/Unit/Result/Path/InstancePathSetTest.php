<?php

declare(strict_types=1);

namespace Asmblah\HeapWalk\Tests\Unit\Result\Path;

use Asmblah\HeapWalk\Result\Path\InstancePathSet;
use Asmblah\HeapWalk\Result\Path\PathInterface;
use Asmblah\HeapWalk\Tests\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;

/**
 * Class InstancePathSetTest.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
class InstancePathSetTest extends TestCase
{
    use ProphecyTrait;

    /**
     * @var object
     */
    private $instance;
    /**
     * @var ObjectProphecy&PathInterface
     */
    private $path1;
    /**
     * @var ObjectProphecy&PathInterface
     */
    private $path2;
    /**
     * @var InstancePathSet
     */
    private $pathSet;

    public function setUp(): void
    {
        $this->instance = $this->prophesize();
        $this->path1 = $this->prophesize(PathInterface::class);
        $this->path2 = $this->prophesize(PathInterface::class);

        $this->path1->toString()->willReturn('my first path');
        $this->path2->toString()->willReturn('my second path');

        $this->pathSet = new InstancePathSet(
            $this->instance->reveal(),
            [$this->path1->reveal(), $this->path2->reveal()]
        );
    }

    public function testDiffReturnsDifferentPaths(): void
    {
        $path1 = $this->prophesize(PathInterface::class);
        $path2 = $this->prophesize(PathInterface::class);
        $path1->toString()->willReturn('my first path');
        $path2->toString()->willReturn('my different second path');
        $otherPathSet = new InstancePathSet(
            $this->instance->reveal(),
            [$path1->reveal(), $path2->reveal()]
        );

        $result = $this->pathSet->diff($otherPathSet);

        static::assertSame($this->instance->reveal(), $result->getEventualValue());
        static::assertCount(1, $result->getPaths());
        static::assertSame('my second path', $result->getPaths()[0]->toString());
    }

    public function testGetEventualValueReturnsTheInstance(): void
    {
        static::assertSame($this->instance->reveal(), $this->pathSet->getEventualValue());
    }

    public function testToArray(): void
    {
        $result = $this->pathSet->toArray();

        static::assertSame(get_class($this->instance->reveal()), $result['class']);
        static::assertEquals(
            [
                'my first path',
                'my second path',
            ],
            $result['paths']
        );
    }
}
