<?php

declare(strict_types=1);

namespace Asmblah\HeapWalk\Tests\Acceptance\Fixtures\Classes;

class Thing
{
    /**
     * @var string
     */
    private $description;
    /**
     * @var mixed
     */
    public $value;

    /**
     * @param string $description
     */
    public function __construct(string $description)
    {
        $this->description = $description;
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param mixed $value
     */
    public function setValue($value): void
    {
        $this->value = $value;
    }
}
