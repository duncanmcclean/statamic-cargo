<?php

namespace DuncanMcClean\Cargo\Subscribers;

use DuncanMcClean\Cargo\Events\OrderCreated;
use DuncanMcClean\Cargo\Events\OrderRefunded;
use DuncanMcClean\Cargo\Events\OrderSaved;
use DuncanMcClean\Cargo\Orders\TimelineEventTypes\OrderStatusChanged;
use Illuminate\Support\Arr;
use Statamic\Events\Subscriber;

class RecordTimelineEvents extends Subscriber
{
    protected $listeners = [
        OrderCreated::class => 'handleOrderCreated',
        OrderSaved::class => 'handleOrderSaved',
        OrderRefunded::class => 'handleOrderRefunded',
    ];

    public function handleOrderCreated(OrderCreated $event): void
    {
        $event->order->appendTimelineEvent('order_created');
    }

    public function handleOrderSaved(OrderSaved $event): void
    {
        $original = $event->order->getOriginal();
        $current = $event->order->getCurrentDirtyStateAttributes();

        // Don't record updates if this is a new order (OrderCreated handles that)
        if (empty($original)) {
            return;
        }

        // Check if status has changed
        if (Arr::get($original, 'status') !== Arr::get($current, 'status')) {
            $event->order->appendTimelineEvent(OrderStatusChanged::class, [
                'original' => Arr::get($original, 'status'),
                'new' => Arr::get($current, 'status'),
            ]);
        } else {
            // If status hasn't changed, it's just a general update
            $event->order->appendTimelineEvent('order_updated');
        }
    }

    public function handleOrderRefunded(OrderRefunded $event): void
    {
        $event->order->appendTimelineEvent('order_refunded', [
            'amount' => $event->refundAmount,
        ]);
    }
}
