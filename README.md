# PHP userland heap walker

[![Build Status](https://github.com/asmblah/php-userland-heap-walker/workflows/CI/badge.svg)](https://github.com/asmblah/php-userland-heap-walker/actions?query=workflow%3ACI)

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
// Find all instances of Item and how to reach them.
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
// Find all instances of Item and how to reach them.
$pathSets = $heapWalk->getInstancePathSets([Item::class]);

// Inspect the result as needed.
assert(count($pathSets) === 1);
assert($pathSets[0] instanceof InstancePathSet);
assert(count($pathSets[0]->getPaths()) === 1);
assert($pathSets[0]->getPaths()[0]->toString() === 'Bag::$items[0]');
assert($pathSets[0]->getPaths()[0]->getEventualValue() instanceof Item);
assert($pathSets[0]->getPaths()[0]->getEventualValue()->description === 'a cabbage');
```

## Caveats & limitations

- Scopes other than the global one are not inspected.
  It should be possible to inspect the arguments of the current call stack
  using the output of `debug_backtrace()`, but that is not yet implemented.

- Values bound to `Closure->$this` or inherited by Closures via `use (...)`
  with no other references will not be discovered.

- The local scope of paused [Generators](https://www.php.net/manual/en/language.generators.overview.php) is not accessible. For example,
  if a paused generator has an iterator variable `$i` declared inside with no
  other references to it existing, it will not be discovered.

- If a captured object is a descendant of another uncaptured object,
  then recursion handling will (currently) mean that only the first
  path to the captured object via the uncaptured one will be recorded.
  eg. a service in Symfony container will only be shown via `$kernel->bundles->...->container[...]`
      and not also via `$kernel->container[...]`.

- Internal references between PHP objects, that are not exposed to userland,
  are not discoverable. For example, a `PDOStatement` has an internal strong
  reference to its `PDOConnection`, but there is no way to access the `PDOConnection`
  from the `PDOStatement`.
  It should be possible to use the [uopz extension](https://www.php.net/manual/en/book.uopz.php)
  to hook `PDOConnection->prepare(...)` and link back to it from the `PDOStatement` (in a future
  plugin for this tool, for example), however this must be done carefully to avoid preventing
  the `PDOConnection` from being GC'd.
