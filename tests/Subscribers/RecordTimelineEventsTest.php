<?php

namespace Tests\Subscribers;

use DuncanMcClean\Cargo\Events\OrderCreated;
use DuncanMcClean\Cargo\Events\OrderRefunded;
use DuncanMcClean\Cargo\Events\OrderStatusUpdated;
use DuncanMcClean\Cargo\Facades\Order;
use DuncanMcClean\Cargo\Orders\OrderStatus;
use Illuminate\Support\Carbon;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Testing\Concerns\PreventsSavingStacheItemsToDisk;
use Tests\TestCase;

class RecordTimelineEventsTest extends TestCase
{
    use PreventsSavingStacheItemsToDisk;

    #[Test]
    public function timeline_event_is_created_when_order_created_event_is_dispatched()
    {
        Carbon::setTestNow(Carbon::parse('2025-01-15 12:00:00'));

        $order = Order::make()
            ->orderNumber('1234')
            ->status(OrderStatus::PaymentPending);

        OrderCreated::dispatch($order);

        $events = $order->timelineEvents();

        $this->assertCount(1, $events);
        $this->assertEquals('order_created', $events->first()->type());
        $this->assertEquals(Carbon::parse('2025-01-15 12:00:00'), $events->first()->timestamp());
    }

    #[Test]
    public function timeline_event_is_created_when_order_status_updated_event_is_dispatched()
    {
        Carbon::setTestNow(Carbon::parse('2025-01-15 12:00:00'));

        $order = Order::make()
            ->orderNumber('1234')
            ->status(OrderStatus::PaymentPending);

        OrderStatusUpdated::dispatch(
            $order,
            OrderStatus::PaymentPending,
            OrderStatus::Shipped
        );

        $events = $order->timelineEvents();

        $this->assertCount(1, $events);
        $this->assertEquals('order_status_changed', $events->first()->type());
        $this->assertEquals('payment_pending', $events->first()->metadata('original'));
        $this->assertEquals('shipped', $events->first()->metadata('new'));
        $this->assertEquals(Carbon::parse('2025-01-15 12:00:00'), $events->first()->timestamp());
    }

    #[Test]
    public function timeline_event_is_created_when_order_refunded_event_is_dispatched()
    {
        Carbon::setTestNow(Carbon::parse('2025-01-15 12:00:00'));

        $order = Order::make()
            ->orderNumber('1234')
            ->status(OrderStatus::PaymentReceived);

        OrderRefunded::dispatch($order, 1500);

        $events = $order->timelineEvents();

        $this->assertCount(1, $events);
        $this->assertEquals('order_refunded', $events->first()->type());
        $this->assertEquals(1500, $events->first()->metadata('amount'));
        $this->assertEquals(Carbon::parse('2025-01-15 12:00:00'), $events->first()->timestamp());
    }
}
