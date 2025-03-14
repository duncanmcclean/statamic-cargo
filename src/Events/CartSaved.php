<?php

namespace DuncanMcClean\Cargo\Events;

use DuncanMcClean\Cargo\Contracts\Cart\Cart;
use Statamic\Contracts\Git\ProvidesCommitMessage;

class CartSaved implements ProvidesCommitMessage
{
    public function __construct(public Cart $cart) {}

    public function commitMessage()
    {
        return __('Cart saved', [], config('statamic.git.locale'));
    }
}
