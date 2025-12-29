<?php

namespace Tests\Orders;

use DuncanMcClean\Cargo\Events\OrderCreated;
use DuncanMcClean\Cargo\Events\OrderRefunded;
use DuncanMcClean\Cargo\Events\OrderSaved;
use DuncanMcClean\Cargo\Facades\Order;
use DuncanMcClean\Cargo\Orders\OrderStatus;
use DuncanMcClean\Cargo\Orders\TimelineEvent;
use DuncanMcClean\Cargo\Orders\TimelineEventTypes\OrderCreated as OrderCreatedEventType;
use DuncanMcClean\Cargo\Orders\TimelineEventTypes\OrderStatusChanged;
use DuncanMcClean\Cargo\Orders\TimelineEventTypes\OrderUpdated;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Event;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\User;
use Statamic\Testing\Concerns\PreventsSavingStacheItemsToDisk;
use Tests\TestCase;

class TimelineEventsTest extends TestCase
{
    use PreventsSavingStacheItemsToDisk;

    #[Test]
    public function can_append_timeline_event()
    {
        Carbon::setTestNow(Carbon::parse('2025-01-15 12:00:00'));

        $order = Order::make()
            ->orderNumber('1234')
            ->status(OrderStatus::PaymentPending);

        $order->appendTimelineEvent('order_created', [
            'foo' => 'bar',
        ]);

        $events = $order->timelineEvents();

        $this->assertCount(1, $events);
        $this->assertEquals('order_created', $events->first()->type());
        $this->assertEquals(Carbon::parse('2025-01-15 12:00:00'), $events->first()->timestamp());
        $this->assertEquals('bar', $events->first()->metadata('foo'));
        $this->assertNull($events->first()->user());
    }

    #[Test]
    public function can_append_timeline_event_with_user()
    {
        Carbon::setTestNow(Carbon::parse('2025-01-15 12:00:00'));

        $user = User::make()->id('test-user')->save();
        $this->actingAs($user);

        $order = Order::make()
            ->orderNumber('1234')
            ->status(OrderStatus::PaymentPending);

        $order->appendTimelineEvent('order_updated');

        $events = $order->timelineEvents();

        $this->assertCount(1, $events);
        $this->assertEquals($user, $events->first()->user());
    }

    #[Test]
    public function can_append_timeline_event_using_event_type_class()
    {
        Carbon::setTestNow(Carbon::parse('2025-01-15 12:00:00'));

        $order = Order::make()
            ->orderNumber('1234')
            ->status(OrderStatus::PaymentPending);

        $order->appendTimelineEvent(OrderStatusChanged::class, [
            'original' => 'payment_pending',
            'new' => 'shipped',
        ]);

        $events = $order->timelineEvents();

        $this->assertCount(1, $events);
        $this->assertEquals('order_status_changed', $events->first()->type());
        $this->assertEquals('payment_pending', $events->first()->metadata('original'));
        $this->assertEquals('shipped', $events->first()->metadata('new'));
    }

    #[Test]
    public function timeline_event_can_get_date()
    {
        Carbon::setTestNow(Carbon::parse('2025-01-15 12:00:00'));

        $order = Order::make()
            ->orderNumber('1234')
            ->status(OrderStatus::PaymentPending);

        $order->appendTimelineEvent('order_created');

        $event = $order->timelineEvents()->first();

        $this->assertInstanceOf(Carbon::class, $event->timestamp());
        $this->assertEquals('2025-01-15 12:00:00', $event->timestamp()->format('Y-m-d H:i:s'));
    }

    #[Test]
    public function timeline_event_can_be_converted_to_array()
    {
        Carbon::setTestNow(Carbon::parse('2025-01-15 12:00:00'));

        $user = User::make()->id('test-user')->save();
        $this->actingAs($user);

        $order = Order::make()
            ->orderNumber('1234')
            ->status(OrderStatus::PaymentPending);

        $order->appendTimelineEvent('order_created', [
            'foo' => 'bar',
        ]);

        $event = $order->timelineEvents()->first();

        $this->assertEquals([
            'timestamp' => Carbon::parse('2025-01-15 12:00:00')->timestamp,
            'type' => 'order_created',
            'user' => 'test-user',
            'metadata' => [
                'foo' => 'bar',
            ],
        ], $event->toArray());
    }

    #[Test]
    public function timeline_event_is_created_when_order_is_created()
    {
        Carbon::setTestNow(Carbon::parse('2025-01-15 12:00:00'));

        $order = Order::make()
            ->orderNumber('1234')
            ->status(OrderStatus::PaymentPending);
        
        $order->save();

        $order = $order->fresh();

        $this->assertCount(1, $order->timelineEvents());
        $this->assertEquals('order_created', $order->timelineEvents()->first()->type());
    }

    #[Test]
    public function timeline_event_is_created_when_order_is_updated()
    {
        Carbon::setTestNow(Carbon::parse('2025-01-15 12:00:00'));

        $order = Order::make()
            ->orderNumber('1234')
            ->status(OrderStatus::PaymentPending);
        
        $order->save();

        $order->set('notes', 'Test notes')->save();

        $order = $order->fresh();

        $this->assertCount(2, $order->timelineEvents());
        $this->assertEquals('order_created', $order->timelineEvents()->get(0)->type());
        $this->assertEquals('order_updated', $order->timelineEvents()->get(1)->type());
    }

    #[Test]
    public function timeline_event_is_created_when_order_status_changes()
    {
        Carbon::setTestNow(Carbon::parse('2025-01-15 12:00:00'));

        $order = Order::make()
            ->orderNumber('1234')
            ->status(OrderStatus::PaymentPending);
        
        $order->save();

        $order->status(OrderStatus::Shipped)->save();

        $order = $order->fresh();

        $this->assertCount(2, $order->timelineEvents());
        $this->assertEquals('order_created', $order->timelineEvents()->get(0)->type());
        $this->assertEquals('order_status_changed', $order->timelineEvents()->get(1)->type());
        $this->assertEquals('payment_pending', $order->timelineEvents()->get(1)->metadata('original'));
        $this->assertEquals('shipped', $order->timelineEvents()->get(1)->metadata('new'));
    }

    #[Test]
    public function timeline_event_is_created_when_order_is_refunded()
    {
        Carbon::setTestNow(Carbon::parse('2025-01-15 12:00:00'));

        $order = Order::make()
            ->orderNumber('1234')
            ->status(OrderStatus::PaymentReceived);
        
        $order->save();

        OrderRefunded::dispatch($order, 1000);

        $order = $order->fresh();

        $this->assertCount(2, $order->timelineEvents());
        $this->assertEquals('order_created', $order->timelineEvents()->get(0)->type());
        $this->assertEquals('order_refunded', $order->timelineEvents()->get(1)->type());
        $this->assertEquals(1000, $order->timelineEvents()->get(1)->metadata('amount'));
    }

    #[Test]
    public function order_created_event_type_generates_message()
    {
        $event = TimelineEvent::make([
            'timestamp' => now()->timestamp,
            'type' => 'order_created',
        ]);

        $order = Order::make();

        $eventType = OrderCreatedEventType::make($event, $order);

        $this->assertEquals(__('cargo::messages.timeline_events.order_created'), $eventType->message());
    }

    #[Test]
    public function order_status_changed_event_type_generates_message()
    {
        $event = TimelineEvent::make([
            'timestamp' => now()->timestamp,
            'type' => 'order_status_changed',
            'metadata' => [
                'original' => 'payment_pending',
                'new' => 'shipped',
            ],
        ]);

        $order = Order::make();

        $eventType = OrderStatusChanged::make($event, $order);

        $this->assertEquals(__('cargo::messages.timeline_events.order_status_changed_from_to', [
            'original' => 'Payment Pending',
            'new' => 'Shipped',
        ]), $eventType->message());
    }

    #[Test]
    public function order_updated_event_type_generates_message()
    {
        $event = TimelineEvent::make([
            'timestamp' => now()->timestamp,
            'type' => 'order_updated',
        ]);

        $order = Order::make();

        $eventType = OrderUpdated::make($event, $order);

        $this->assertEquals(__('cargo::messages.timeline_events.order_updated'), $eventType->message());
    }

    #[Test]
    public function timeline_event_type_can_get_handle_from_class()
    {
        $this->assertEquals('order_created', OrderCreatedEventType::handle());
        $this->assertEquals('order_status_changed', OrderStatusChanged::handle());
        $this->assertEquals('order_updated', OrderUpdated::handle());
    }
}
