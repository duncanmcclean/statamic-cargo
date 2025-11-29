<?php

namespace DuncanMcClean\Cargo\Orders\TimelineEventTypes;

use DuncanMcClean\Cargo\Orders\OrderStatus;
use DuncanMcClean\Cargo\Orders\TimelineEventType;

class OrderStatusChanged extends TimelineEventType
{
    public function message(): string
    {
        $newStatus = $this->event->metadata('new');
        $originalStatus = $this->event->metadata('original');

        if ($newStatus && $originalStatus) {
            $newStatusLabel = OrderStatus::label(OrderStatus::from($newStatus));
            $originalStatusLabel = OrderStatus::label(OrderStatus::from($originalStatus));

            return "Order status changed from {$originalStatusLabel} to {$newStatusLabel}";
        }

        if ($newStatus) {
            $newStatusLabel = OrderStatus::label(OrderStatus::from($newStatus));

            return "Order status changed to {$newStatusLabel}";
        }

        return 'Order status changed';
    }
}
