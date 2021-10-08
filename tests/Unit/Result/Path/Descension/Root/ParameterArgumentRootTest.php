<?php

declare(strict_types=1);

namespace Asmblah\HeapWalk\Tests\Unit\Result\Path\Descension\Root;

use Asmblah\HeapWalk\Result\Path\Descension\Root\ParameterArgumentRoot;
use Asmblah\HeapWalk\Tests\TestCase;

/**
 * Class ParameterArgumentRootTest.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
class ParameterArgumentRootTest extends TestCase
{
    /**
     * @var ParameterArgumentRoot
     */
    private $root;

    public function setUp(): void
    {
        $this->root = new ParameterArgumentRoot('My\\Stuff\\my_func', 'my value');
    }

    public function testGetName(): void
    {
        static::assertSame('My\\Stuff\\my_func(<arg>)', $this->root->getName());
    }

    public function testGetValue(): void
    {
        static::assertSame('my value', $this->root->getValue());
    }

    public function testToArray(): void
    {
        static::assertEquals(
            [
                'function' => 'My\\Stuff\\my_func',
                'value' => 'my value',
            ],
            $this->root->toArray()
        );
    }

    public function testToString(): void
    {
        static::assertSame('My\\Stuff\\my_func(<arg>)', $this->root->toString());
    }
}
