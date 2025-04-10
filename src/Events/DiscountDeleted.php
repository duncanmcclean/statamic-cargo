<?php

namespace DuncanMcClean\Cargo\Events;

use DuncanMcClean\Cargo\Contracts\Discounts\Discount;
use Statamic\Contracts\Git\ProvidesCommitMessage;
use Statamic\Events\Event;

class DiscountDeleted extends Event implements ProvidesCommitMessage
{
    public function __construct(public Discount $discount) {}

    public function commitMessage()
    {
        return __('Discount deleted', [], config('statamic.git.locale'));
    }
}
