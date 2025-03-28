<?php

namespace DuncanMcClean\Cargo\Events;

use Statamic\Events\Event;

class ProductStockLow extends Event
{
    public function __construct(public $product) {}
}
