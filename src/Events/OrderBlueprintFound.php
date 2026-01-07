<?php

namespace DuncanMcClean\Cargo\Events;

use Statamic\Events\Event;

class OrderBlueprintFound extends Event
{
    public function __construct(public $blueprint) {}
}
