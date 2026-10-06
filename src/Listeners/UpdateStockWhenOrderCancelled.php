<?php

namespace DuncanMcClean\Cargo\Listeners;

use DuncanMcClean\Cargo\Events\OrderStatusUpdated;
use DuncanMcClean\Cargo\Orders\OrderStatus;
use DuncanMcClean\Cargo\Products\Actions\RestoreStock;
use DuncanMcClean\Cargo\Products\Actions\UpdateStock;

class UpdateStockWhenOrderCancelled
{
    public function handle(OrderStatusUpdated $event): void
    {
        if ($event->updatedStatus === OrderStatus::Cancelled) {
            app(RestoreStock::class)->handle($event->order);
        }

        if ($event->originalStatus === OrderStatus::Cancelled) {
            app(UpdateStock::class)->handle($event->order);
        }
    }
}
