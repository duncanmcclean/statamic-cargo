<?php

namespace DuncanMcClean\Cargo\Events;

use DuncanMcClean\Cargo\Contracts\Purchasable;
use Statamic\Events\Event;

class ProductStockLow extends Event
{
    public function __construct(public Purchasable $purchasable) {}
}
