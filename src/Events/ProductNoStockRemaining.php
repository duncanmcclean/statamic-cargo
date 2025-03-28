<?php

namespace DuncanMcClean\Cargo\Events;

use Statamic\Events\Event;

class ProductNoStockRemaining extends Event
{
    public function __construct(public $product) {}
}
