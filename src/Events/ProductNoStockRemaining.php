<?php

namespace DuncanMcClean\Cargo\Events;

use DuncanMcClean\Cargo\Contracts\Purchasable;
use Statamic\Events\Event;

class ProductNoStockRemaining extends Event
{
    public function __construct(public Purchasable $purchasable) {}
}
