<?php

namespace DuncanMcClean\Cargo\Events;

use DuncanMcClean\Cargo\Contracts\Discounts\Discount;
use Statamic\Events\Event;

class DiscountCreated extends Event
{
    public function __construct(public Discount $discount) {}
}
