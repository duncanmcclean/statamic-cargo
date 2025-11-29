<?php

namespace DuncanMcClean\Cargo\Orders\TimelineEventTypes;

use DuncanMcClean\Cargo\Orders\TimelineEventType;

class OrderRefunded extends TimelineEventType
{
    public function message(): string
    {
        $amount = $this->event->metadata('amount');

        if ($amount) {
            $formattedAmount = number_format($amount / 100, 2);

            return "Order was refunded for {$formattedAmount}";
        }

        return 'Order was refunded';
    }
}
