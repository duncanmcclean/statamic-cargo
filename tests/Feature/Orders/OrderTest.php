<?php

namespace Tests\Feature\Orders;

use DuncanMcClean\Cargo\Contracts\Orders\Order as OrderContract;
use DuncanMcClean\Cargo\Events\OrderCancelled;
use DuncanMcClean\Cargo\Events\OrderCreated;
use DuncanMcClean\Cargo\Events\OrderDeleted;
use DuncanMcClean\Cargo\Events\OrderPaymentPending;
use DuncanMcClean\Cargo\Events\OrderPaymentReceived;
use DuncanMcClean\Cargo\Events\OrderReturned;
use DuncanMcClean\Cargo\Events\OrderSaved;
use DuncanMcClean\Cargo\Events\OrderShipped;
use DuncanMcClean\Cargo\Facades\Cart;
use DuncanMcClean\Cargo\Facades\Order;
use DuncanMcClean\Cargo\Orders\OrderStatus;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Event;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Testing\Concerns\PreventsSavingStacheItemsToDisk;
use Tests\TestCase;

class OrderTest extends TestCase
{
    use OrderQueryTests, PreventsSavingStacheItemsToDisk;

    #[Test]
    public function can_make_order_from_cart()
    {
        $cart = Cart::make()
            ->id('abc')
            ->lineItems([
                [
                    'product' => '123',
                    'quantity' => 1,
                    'total' => 2500,
                ],
            ])
            ->grandTotal(2500)
            ->subTotal(2500)
            ->set('foo', 'bar')
            ->set('baz', 'foobar');

        $order = Order::makeFromCart($cart);

        $this->assertInstanceOf(OrderContract::class, $order);

        $this->assertEquals($cart->lineItems(), $order->lineItems());
        $this->assertEquals(2500, $order->grandTotal());
        $this->assertEquals(2500, $order->subTotal());
        $this->assertEquals(0, $order->discountTotal());
        $this->assertEquals(0, $order->taxTotal());
        $this->assertEquals(0, $order->shippingTotal());
        $this->assertEquals('bar', $order->get('foo'));
        $this->assertEquals('foobar', $order->get('baz'));
    }

    #[Test]
    public function can_generate_order_number()
    {
        Order::make()->orderNumber(1000)->save();
        Order::make()->orderNumber(1001)->save();
        Order::make()->orderNumber(1002)->save();

        $order = tap(Order::make())->save();

        $this->assertEquals(1003, $order->orderNumber());
    }

    #[Test]
    public function order_can_be_saved()
    {
        Event::fake();

        $this->assertNull(Order::find('abc'));

        $order = Order::make()
            ->id('abc')
            ->orderNumber(1000)
            ->date(Carbon::parse('2025-03-15 12:34:56'));

        $order->save();

        $this->assertInstanceOf(OrderContract::class, $order = Order::find($order->id()));
        $this->assertEquals('abc', $order->id());
        $this->assertFileExists($order->path());
        $this->assertStringContainsString('content/cargo/orders/2025-03-15-123456.1000.yaml', $order->path());

        $this->assertEquals(<<<'YAML'
id: abc
status: payment_pending

YAML
            , file_get_contents($order->path()));

        Event::assertDispatched(OrderCreated::class, function ($event) use ($order) {
            return $event->order->id() === $order->id();
        });

        Event::assertDispatched(OrderSaved::class, function ($event) use ($order) {
            return $event->order->id() === $order->id();
        });

        Event::assertDispatched(OrderPaymentPending::class, function ($event) use ($order) {
            return $event->order->id() === $order->id();
        });
    }

    #[Test]
    public function order_can_be_saved_quietly()
    {
        Event::fake();

        $this->assertNull(Order::find('abc'));

        $order = Order::make()
            ->id('abc')
            ->orderNumber(1000)
            ->date(Carbon::parse('2025-03-15 12:34:56'));

        $order->saveQuietly();

        $this->assertInstanceOf(OrderContract::class, $order = Order::find($order->id()));
        $this->assertEquals('abc', $order->id());
        $this->assertFileExists($order->path());
        $this->assertStringContainsString('content/cargo/orders/2025-03-15-123456.1000.yaml', $order->path());

        $this->assertEquals(<<<'YAML'
id: abc
status: payment_pending

YAML
            , file_get_contents($order->path()));

        Event::assertNotDispatched(OrderCreated::class, function ($event) use ($order) {
            return $event->order->id() === $order->id();
        });

        Event::assertNotDispatched(OrderSaved::class, function ($event) use ($order) {
            return $event->order->id() === $order->id();
        });

        Event::assertNotDispatched(OrderPaymentPending::class, function ($event) use ($order) {
            return $event->order->id() === $order->id();
        });
    }

    #[Test]
    public function order_payment_pending_event_is_dispatched()
    {
        Event::fake();

        // Event should be dispatched when an order is created
        // (payment pending is the default status)
        $order = tap(Order::make())->save();

        Event::assertDispatched(OrderPaymentPending::class, function ($event) use ($order) {
            return $event->order->id() === $order->id();
        });
    }

    #[Test]
    public function order_payment_received_event_is_dispatched()
    {
        Event::fake();

        $order = tap(Order::make())->save();
        $order->status(OrderStatus::PaymentReceived)->save();

        Event::assertDispatched(OrderPaymentReceived::class, function ($event) use ($order) {
            return $event->order->id() === $order->id();
        });
    }

    #[Test]
    public function order_shipped_event_is_dispatched()
    {
        Event::fake();

        $order = tap(Order::make())->save();
        $order->status(OrderStatus::Shipped)->save();

        Event::assertDispatched(OrderShipped::class, function ($event) use ($order) {
            return $event->order->id() === $order->id();
        });
    }

    #[Test]
    public function order_returned_event_is_dispatched()
    {
        Event::fake();

        $order = tap(Order::make())->save();
        $order->status(OrderStatus::Returned)->save();

        Event::assertDispatched(OrderReturned::class, function ($event) use ($order) {
            return $event->order->id() === $order->id();
        });
    }

    #[Test]
    public function order_cancelled_event_is_dispatched()
    {
        Event::fake();

        $order = tap(Order::make())->save();
        $order->status(OrderStatus::Cancelled)->save();

        Event::assertDispatched(OrderCancelled::class, function ($event) use ($order) {
            return $event->order->id() === $order->id();
        });
    }

    #[Test]
    public function order_status_events_are_not_dispatched_when_status_stays_the_same()
    {
        $order = tap(Order::make()->status(OrderStatus::Shipped))->save();

        Event::fake();

        $order->save();
        Event::assertNotDispatched(OrderShipped::class);

        $order->status(OrderStatus::Shipped)->save();
        Event::assertNotDispatched(OrderShipped::class);
    }

    #[Test]
    public function order_can_be_deleted()
    {
        Event::fake();

        $order = tap(Order::make())->save();

        $this->assertFileExists($order->path());

        $order->delete();

        $this->assertFileDoesNotExist($order->path());

        Event::assertDispatched(OrderDeleted::class, function ($event) use ($order) {
            return $event->order->id() === $order->id();
        });
    }

    #[Test]
    public function order_can_be_deleted_quietly()
    {
        Event::fake();

        $order = tap(Order::make())->save();

        $this->assertFileExists($order->path());

        $order->deleteQuietly();

        $this->assertFileDoesNotExist($order->path());

        Event::assertNotDispatched(OrderDeleted::class, function ($event) use ($order) {
            return $event->order->id() === $order->id();
        });
    }
}
