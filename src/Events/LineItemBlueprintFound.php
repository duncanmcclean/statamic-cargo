<?php

namespace DuncanMcClean\Cargo\Events;

use Statamic\Events\Event;

class LineItemBlueprintFound extends Event
{
    public function __construct(public $blueprint) {}
}
