<?php

namespace DuncanMcClean\Cargo\Events;

use DuncanMcClean\Cargo\Contracts\Cart\Cart;
use Statamic\Contracts\Git\ProvidesCommitMessage;
use Statamic\Events\Event;

class CartRecalculated extends Event implements ProvidesCommitMessage
{
    public function __construct(public Cart $cart) {}

    public function commitMessage()
    {
        return __('Cart recalculated', [], config('statamic.git.locale'));
    }
}
