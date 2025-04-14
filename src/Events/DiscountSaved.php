<?php

namespace DuncanMcClean\Cargo\Events;

use DuncanMcClean\Cargo\Contracts\Discounts\Discount;
use Statamic\Contracts\Git\ProvidesCommitMessage;
use Statamic\Events\Event;

class DiscountSaved extends Event implements ProvidesCommitMessage
{
    public function __construct(public Discount $discount) {}

    public function commitMessage()
    {
        return __('Discount saved', [], config('statamic.git.locale'));
    }
}
