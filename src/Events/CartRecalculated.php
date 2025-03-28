<?php

namespace DuncanMcClean\Cargo\Events;

use DuncanMcClean\Cargo\Contracts\Cart\Cart;
use Statamic\Events\Event;

class CartRecalculated extends Event
{
    public function __construct(public Cart $cart) {}
}
