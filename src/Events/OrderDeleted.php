<?php

namespace DuncanMcClean\Cargo\Events;

use DuncanMcClean\Cargo\Contracts\Orders\Order;
use Statamic\Contracts\Git\ProvidesCommitMessage;

class OrderDeleted implements ProvidesCommitMessage
{
    public function __construct(public Order $order) {}

    public function commitMessage()
    {
        return __('Order deleted', [], config('statamic.git.locale'));
    }
}
