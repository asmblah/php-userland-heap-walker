<?php

declare(strict_types=1);

namespace Asmblah\HeapWalk\Tests\Acceptance\Fixtures\Classes;

class NormalSubThing extends Thing
{
    /**
     * Simulates the scenario when ocramius/proxy-manager is in use,
     * and the properties that should be inherited from the parent class
     * do not exist at all for the instance.
     */
    public function unsetPrivateDescriptionProperty(): void
    {
        (function () {
            unset($this->description);
        })->bindTo($this, Thing::class)();
    }
}
