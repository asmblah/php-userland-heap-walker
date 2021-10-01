<?php

declare(strict_types=1);

namespace Asmblah\HeapWalk\Result\Path;

use Asmblah\HeapWalk\Result\Path\Descension\DescensionInterface;

/**
 * Class Path.
 *
 * Groups a series of successive descensions together to form a path from a root to an eventual value.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
class Path implements PathInterface
{
    /**
     * @var DescensionInterface[]
     */
    private $descensions;

    /**
     * @param DescensionInterface[] $descensions
     */
    public function __construct(array $descensions)
    {
        $this->descensions = $descensions;
    }

    /**
     * @inheritDoc
     */
    public function getDescensions(): array
    {
        return $this->descensions;
    }

    /**
     * @inheritDoc
     */
    public function getEventualValue()
    {
        return $this->descensions[count($this->descensions) - 1]->getValue();
    }

    /**
     * @inheritDoc
     */
    public function toArray(): array
    {
        return array_map(
            function (DescensionInterface $descension): array {
                return $descension->toArray();
            },
            $this->descensions
        );
    }

    /**
     * @inheritDoc
     */
    public function toString(): string
    {
        return implode(
            '',
            array_map(
                function (DescensionInterface $descension): string {
                    return $descension->toString();
                },
                $this->descensions
            )
        );
    }
}
