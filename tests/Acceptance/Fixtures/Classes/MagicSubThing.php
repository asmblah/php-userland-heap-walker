<?php

declare(strict_types=1);

namespace Asmblah\HeapWalk\Tests\Acceptance\Fixtures\Classes;

use InvalidArgumentException;

class MagicSubThing extends Thing
{
    public function __get(string $propertyName)
    {
        switch ($propertyName) {
            case 'description':
                return 'I am shadowing Thing::$description';
            case 'value':
                return 'I am shadowing Thing::$value';
            default:
                throw new InvalidArgumentException('Unexpected property name "' . $propertyName . '"');
        }
    }
}
