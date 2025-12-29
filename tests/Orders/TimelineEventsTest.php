<?php

namespace Tests\Orders;

use DuncanMcClean\Cargo\Contracts\Orders\Order as OrderContract;
use DuncanMcClean\Cargo\Events\OrderCreated;
use DuncanMcClean\Cargo\Events\OrderRefunded;
use DuncanMcClean\Cargo\Events\OrderStatusUpdated;
use DuncanMcClean\Cargo\Facades\Order;
use DuncanMcClean\Cargo\Orders\OrderStatus;
use DuncanMcClean\Cargo\Orders\TimelineEvent;
use DuncanMcClean\Cargo\Orders\TimelineEventType;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\User;
use Statamic\Testing\Concerns\PreventsSavingStacheItemsToDisk;
use Tests\TestCase;
use DuncanMcClean\Cargo\Orders\TimelineEventTypes;

class TimelineEventsTest extends TestCase
{
    use PreventsSavingStacheItemsToDisk;

    #[Test]
    public function can_get_timeline_events()
    {
        $user = User::make()->save();

        $order = Order::make()->set('timeline_events', [
            [
                'timestamp' => 1767019337,
                'type' => 'order_created',
            ],
            [
                'timestamp' => 1767019338,
                'type' => 'order_updated',
                'user' => $user->id(),
            ],
            [
                'timestamp' => 1767019339,
                'type' => 'order_status_changed',
                'user' => $user->id(),
                'metadata' => [
                    'Original Status' => 'payment_pending',
                    'New Status' => 'payment_received',
                ],
            ],
        ]);

        $timelineEvents = $order->timelineEvents();

        $this->assertCount(3, $timelineEvents);
        $this->assertInstanceOf(Collection::class, $timelineEvents);

        $this->assertInstanceOf(TimelineEvent::class, $timelineEvents->get(0));
        $this->assertEquals('2025-12-29 14:42:17', $timelineEvents->get(0)->timestamp()->format('Y-m-d H:i:s'));
        $this->assertInstanceOf(TimelineEventTypes\OrderCreated::class, $timelineEvents->get(0)->type());
        $this->assertNull($timelineEvents->get(0)->user());
        $this->assertEquals([], $timelineEvents->get(0)->metadata()->all());

        $this->assertInstanceOf(TimelineEvent::class, $timelineEvents->get(1));
        $this->assertEquals('2025-12-29 14:42:18', $timelineEvents->get(1)->timestamp()->format('Y-m-d H:i:s'));
        $this->assertInstanceOf(TimelineEventTypes\OrderUpdated::class, $timelineEvents->get(1)->type());
        $this->assertEquals($user, $timelineEvents->get(1)->user());
        $this->assertEquals([], $timelineEvents->get(1)->metadata()->all());

        $this->assertInstanceOf(TimelineEvent::class, $timelineEvents->get(2));
        $this->assertEquals('2025-12-29 14:42:19', $timelineEvents->get(2)->timestamp()->format('Y-m-d H:i:s'));
        $this->assertInstanceOf(TimelineEventTypes\OrderStatusChanged::class, $timelineEvents->get(2)->type());
        $this->assertEquals($user, $timelineEvents->get(2)->user());
        $this->assertEquals([
            'Original Status' => 'payment_pending',
            'New Status' => 'payment_received',
        ], $timelineEvents->get(2)->metadata()->all());
    }

    #[Test]
    public function can_append_timeline_event()
    {
        Carbon::setTestNow(Carbon::parse('2025-01-15 12:00:00'));

        $this->actingAs(User::make()->id('foo')->makeSuper()->save());

        $order = Order::make();

        $order->appendTimelineEvent(SomethingHappened::class, [
            'foo' => 'bar',
        ]);

        $this->assertEquals([
            'timestamp' => '1736942400',
            'type' => 'something_happened',
            'user' => 'foo',
            'metadata' => ['foo' => 'bar'],
        ], $order->get('timeline_events')[0]);
    }

    #[Test]
    public function can_append_timeline_event_without_user_and_metadata()
    {
        Carbon::setTestNow(Carbon::parse('2025-01-15 12:00:00'));

        $order = Order::make();

        $order->appendTimelineEvent(SomethingHappened::class);

        $this->assertEquals([
            'timestamp' => '1736942400',
            'type' => 'something_happened',
        ], $order->get('timeline_events')[0]);
    }

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
                'Original Status' => 'payment_pending',
                'New Status' => 'shipped',
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
                'Original Status' => 'payment_pending',
                'New Status' => 'shipped',
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
                'Amount' => 1500,
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

class SomethingHappened extends TimelineEventType
{
    public function message(): string
    {
        return 'Something happened!';
    }
}
