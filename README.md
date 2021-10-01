# PHP userland heap walker

Walks as much of the userland heap as possible, looking for instances of the given FQCN (Fully-Qualified Class Name).

This helps figure out where a given object is being referred to from,
in order to help solve memory leaks.

## Usage

### Install
```shell
composer require --dev asmblah/heap-walker
```

### Use
```php
<?php

// ...

$heapWalk = new HeapWalk();
$pathSets = $heapWalk->getInstancePathSets([Item::class]);

// Inspect the result as needed.
```

### Full example
```php
<?php

use Asmblah\HeapWalk\HeapWalk;
use Asmblah\HeapWalk\Result\Path\InstancePathSet;

require_once __DIR__ . '/vendor/autoload.php';

class Item
{
    public $description;

    public function __construct($description)
    {
        $this->description = $description;
    }
}

class Bag
{
    // Note that visibility is ignored.
    private static $items = [];

    public static function init()
    {
        self::$items[] = new Item('a cabbage');
    }
}

Bag::init();

$heapWalk = new HeapWalk();
$pathSets = $heapWalk->getInstancePathSets([Item::class]);

// Inspect the result as needed.
assert(count($pathSets) === 1);
assert($pathSets[0] instanceof InstancePathSet);
assert(count($pathSets[0]->getPaths()) === 1);
assert($pathSets[0]->getPaths()[0]->toString() === 'Bag::$items[0]');
assert($pathSets[0]->getPaths()[0]->getEventualValue() instanceof Item);
assert($pathSets[0]->getPaths()[0]->getEventualValue()->description === 'a cabbage');
```

## Caveats

Scopes other than the global one are not inspected.
It should be possible to inspect the arguments of the current call stack
using the output of `debug_backtrace()`, but that is not yet implemented.
