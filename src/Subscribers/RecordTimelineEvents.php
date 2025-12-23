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

        if (
            ($originalStatus = Arr::pull($original, 'status'))
            !== ($newStatus = Arr::get($current, 'status'))
        ) {
            $event->order->appendTimelineEvent(OrderStatusChanged::class, [
                'original' => $originalStatus,
                'new' => $newStatus,
            ]);
        }

        if (! empty($original)) {
            $event->order->appendTimelineEvent('order_updated');
        }
    }

    public function handleOrderRefunded(OrderRefunded $event): void
    {
        $event->order->appendTimelineEvent('order_refunded', [
            'amount' => $event->amount,
        ]);
    }
}
