<?php

namespace DuncanMcClean\Cargo\Events;

use DuncanMcClean\Cargo\Contracts\Cart\Cart;
use Statamic\Contracts\Git\ProvidesCommitMessage;
use Statamic\Events\Event;

class CartCreated extends Event
{
    public function __construct(public Cart $cart) {}
}
