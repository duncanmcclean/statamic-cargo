<?php

namespace DuncanMcClean\Cargo\Subscribers;

use DuncanMcClean\Cargo\Events\OrderCreated;
use DuncanMcClean\Cargo\Events\OrderRefunded;
use DuncanMcClean\Cargo\Events\OrderStatusUpdated;
use DuncanMcClean\Cargo\Orders\TimelineEventTypes;
use Statamic\Events\Subscriber;

class RecordTimelineEvents extends Subscriber
{
    protected $listeners = [
        OrderCreated::class => 'handleOrderCreated',
        OrderStatusUpdated::class => 'handleOrderStatusUpdated',
        OrderRefunded::class => 'handleOrderRefunded',
    ];

    public function handleOrderCreated(OrderCreated $event): void
    {
        $event->order->appendTimelineEvent(TimelineEventTypes\OrderCreated::class);
    }

    public function handleOrderStatusUpdated(OrderStatusUpdated $event): void
    {
        $event->order->appendTimelineEvent(TimelineEventTypes\OrderStatusChanged::class, [
            'original' => $event->originalStatus?->value,
            'new' => $event->updatedStatus->value,
        ]);
    }

    public function handleOrderRefunded(OrderRefunded $event): void
    {
        $event->order->appendTimelineEvent(TimelineEventTypes\OrderRefunded::class, [
            'amount' => $event->amount,
        ]);
    }
}
