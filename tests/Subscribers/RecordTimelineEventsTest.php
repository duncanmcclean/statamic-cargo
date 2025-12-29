<?php

namespace Tests\Subscribers;

use DuncanMcClean\Cargo\Contracts\Orders\Order as OrderContract;
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
    public function order_created_event_is_recorded()
    {
        Carbon::setTestNow(Carbon::parse('2025-01-15 12:00:00'));

        $order = Order::make();

        OrderCreated::dispatch($order);

        $this->assertEquals([
            ['timestamp' => 1736942400, 'type' => 'order_created', 'user' => null, 'metadata' => []],
        ], $order->timelineEvents()->toArray());
    }

    #[Test]
    public function order_updated_event_is_recorded()
    {
        Carbon::setTestNow(Carbon::parse('2025-01-15 12:00:00'));

        $order = $this->makeOrder();

        Carbon::setTestNow(Carbon::parse('2025-01-15 12:00:00'));

        ray()->clearScreen();

        $order->set('notes', 'Test notes')->save();

        $this->assertEquals([
            ['timestamp' => 1736942400, 'type' => 'order_created', 'user' => null, 'metadata' => []],
            ['timestamp' => 1736942400, 'type' => 'order_updated', 'user' => null, 'metadata' => [
                'notes' => 'Test notes',
            ]],
        ], $order->timelineEvents()->toArray());
    }

    #[Test]
    public function order_updated_event_is_not_recorded_when_order_was_just_created()
    {
        Carbon::setTestNow(Carbon::parse('2025-01-15 12:00:00'));

        $order = $this->makeOrder();

        $this->assertEquals([
            ['timestamp' => 1736942400, 'type' => 'order_created', 'user' => null, 'metadata' => []],
        ], $order->timelineEvents()->toArray());
    }

    #[Test]
    public function order_updated_event_is_not_recorded_when_only_the_status_was_updated()
    {
        Carbon::setTestNow(Carbon::parse('2025-01-15 12:00:00'));

        $order = $this->makeOrder();

        Carbon::setTestNow(Carbon::parse('2025-01-15 14:00:00'));

        $order->status(OrderStatus::Shipped)->save();

        $this->assertEquals([
            ['timestamp' => 1736942400, 'type' => 'order_created', 'user' => null, 'metadata' => []],
            ['timestamp' => 1736949600, 'type' => 'order_status_changed', 'user' => null, 'metadata' => [
                'original' => 'payment_pending',
                'new' => 'shipped',
            ]],
        ], $order->fresh()->timelineEvents()->toArray());
    }

    #[Test]
    public function order_status_changed_event_is_recorded()
    {
        Carbon::setTestNow(Carbon::parse('2025-01-15 12:00:00'));

        $order = $this->makeOrder();

        Carbon::setTestNow(Carbon::parse('2025-01-15 14:00:00'));

        OrderStatusUpdated::dispatch(
            $order,
            OrderStatus::PaymentPending,
            OrderStatus::Shipped
        );

        $this->assertEquals([
            ['timestamp' => 1736942400, 'type' => 'order_created', 'user' => null, 'metadata' => []],
            ['timestamp' => 1736949600, 'type' => 'order_status_changed', 'user' => null, 'metadata' => [
                'original' => 'payment_pending',
                'new' => 'shipped',
            ]],
        ], $order->timelineEvents()->toArray());
    }

    #[Test]
    public function order_refunded_event_is_recorded()
    {
        Carbon::setTestNow(Carbon::parse('2025-01-15 12:00:00'));

        $order = $this->makeOrder();

        Carbon::setTestNow(Carbon::parse('2025-01-15 14:00:00'));

        OrderRefunded::dispatch($order, 1500);

        $this->assertEquals([
            ['timestamp' => 1736942400, 'type' => 'order_created', 'user' => null, 'metadata' => []],
            ['timestamp' => 1736949600, 'type' => 'order_refunded', 'user' => null, 'metadata' => [
                'amount' => 1500,
            ]],
        ], $order->timelineEvents()->toArray());
    }

    private function makeOrder(): OrderContract
    {
        $order = Order::make()
            ->orderNumber(1000)
            ->status(OrderStatus::PaymentPending)
            ->date(Carbon::now());

        $order->save();

        // We need to clone the $order before returning it, otherwise, Order::find() will
        // change properties on the "current" Order object, which messes up our dirty state checks.
        return clone $order;
    }
}
