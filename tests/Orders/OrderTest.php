<?php

namespace Tests\Orders;

use DuncanMcClean\Cargo\Contracts\Orders\Order as OrderContract;
use DuncanMcClean\Cargo\Customers\GuestCustomer;
use DuncanMcClean\Cargo\Events\OrderCancelled;
use DuncanMcClean\Cargo\Events\OrderCreated;
use DuncanMcClean\Cargo\Events\OrderDeleted;
use DuncanMcClean\Cargo\Events\OrderPaymentPending;
use DuncanMcClean\Cargo\Events\OrderPaymentReceived;
use DuncanMcClean\Cargo\Events\OrderReturned;
use DuncanMcClean\Cargo\Events\OrderSaved;
use DuncanMcClean\Cargo\Events\OrderShipped;
use DuncanMcClean\Cargo\Facades\Order;
use DuncanMcClean\Cargo\Orders\LineItem;
use DuncanMcClean\Cargo\Orders\OrderStatus;
use DuncanMcClean\Cargo\Shipping\ShippingOption;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Event;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Collection;
use Statamic\Facades\Entry;
use Statamic\Facades\User;
use Statamic\Testing\Concerns\PreventsSavingStacheItemsToDisk;
use Tests\Fixtures\ShippingMethods\FakeShippingMethod;
use Tests\TestCase;

class OrderTest extends TestCase
{
    use PreventsSavingStacheItemsToDisk;

    #[Test]
    public function can_get_and_set_guest_customer()
    {
        $order = Order::make();

        $order->customer(['name' => 'CJ Cregg', 'email' => 'cj.cregg@example.com']);

        $this->assertInstanceof(GuestCustomer::class, $order->customer());
        $this->assertEquals('CJ Cregg', $order->customer()->name());
        $this->assertEquals('cj.cregg@example.com', $order->customer()->email());
    }

    #[Test]
    public function can_get_and_set_customer()
    {
        $order = Order::make();
        $user = User::make()->email('cj.cregg@example.com')->set('name', 'CJ Cregg')->save();

        $order->customer($user);

        $this->assertInstanceof(\Statamic\Contracts\Auth\User::class, $order->customer());
        $this->assertEquals('CJ Cregg', $order->customer()->name());
        $this->assertEquals('cj.cregg@example.com', $order->customer()->email());
    }

    #[Test]
    public function can_add_line_item()
    {
        Collection::make('products')->save();
        Entry::make()->id('product-id')->collection('products')->set('product_variants', [
            'variants' => [['name' => 'Sizes', 'values' => ['small']]],
            'options' => [['key' => 'small', 'variant' => 'Small', 'price' => 500]],
        ])->save();

        $order = Order::make();

        $order->lineItems()->create([
            'product' => 'product-id',
            'variant' => 'small',
            'quantity' => 2,
            'total' => 1000,
            'foo' => 'bar',
            'baz' => 'qux',
        ]);

        $this->assertCount(1, $order->lineItems());

        $lineItem = $order->lineItems()->first();

        $this->assertInstanceOf(LineItem::class, $lineItem);
        $this->assertNotNull($lineItem->id());
        $this->assertEquals('product-id', $lineItem->product()->id());
        $this->assertEquals('small', $lineItem->variant()->key());
        $this->assertEquals(2, $lineItem->quantity());
        $this->assertEquals(1000, $lineItem->total());
        $this->assertEquals('bar', $lineItem->data()->get('foo'));
        $this->assertEquals('qux', $lineItem->data()->get('baz'));
    }

    #[Test]
    public function can_update_line_item()
    {
        Collection::make('products')->save();
        Entry::make()->id('product-id')->collection('products')->data(['price' => 500])->save();

        $order = Order::make()->lineItems([
            [
                'id' => 'abc123',
                'product' => 'product-id',
                'quantity' => 2,
                'total' => 1000,
                'foo' => 'bar',
                'baz' => 'qux',
            ],
            [
                'id' => 'def456',
                'product' => 'another-product-id',
                'variant' => 'another-variant-id',
                'quantity' => 1,
                'total' => 2500,
                'bar' => 'baz',
            ],
        ]);

        $order->lineItems()->update('abc123', [
            'product' => 'product-id',
            'quantity' => 1, // This changed...
            'total' => 500, // This changed too...
            'barz' => 'foo', // This is new...
            // And, some other keys were removed...
        ]);

        $lineItem = $order->lineItems()->find('abc123');

        $this->assertEquals(1, $lineItem->quantity());
        $this->assertEquals(500, $lineItem->total());
        $this->assertNull($lineItem->data()->get('foo'));
        $this->assertNull($lineItem->data()->get('baz'));
        $this->assertEquals('foo', $lineItem->data()->get('barz'));
    }

    #[Test]
    public function can_remove_line_item()
    {
        $order = Order::make()->lineItems([
            [
                'id' => 'abc123',
                'product' => 'product-id',
                'quantity' => 2,
                'total' => 1000,
                'foo' => 'bar',
                'baz' => 'qux',
            ],
            [
                'id' => 'def456',
                'product' => 'another-product-id',
                'variant' => 'another-variant-id',
                'quantity' => 1,
                'total' => 2500,
                'bar' => 'baz',
            ],
        ]);

        $this->assertCount(2, $order->lineItems());

        $order->lineItems()->remove('abc123');

        $this->assertCount(1, $order->lineItems());
        $this->assertNull($order->lineItems()->find('abc123'));
        $this->assertNotNull($order->lineItems()->find('def456'));
    }

    #[Test]
    public function can_build_path()
    {
        $order = Order::make()
            ->orderNumber(1234)
            ->date(Carbon::parse('2024-01-01 10:35:10'));

        $this->assertStringContainsString(
            'content/cargo/orders/2024-01-01-103510.1234.yaml',
            $order->buildPath()
        );
    }

    #[Test]
    public function it_returns_the_shipping_method()
    {
        FakeShippingMethod::register();

        config()->set('statamic.cargo.shipping.methods', ['fake_shipping_method' => []]);

        $order = Order::make()->set('shipping_method', 'fake_shipping_method');

        $this->assertInstanceOf(FakeShippingMethod::class, $order->shippingMethod());
    }

    #[Test]
    public function it_returns_the_shipping_option()
    {
        FakeShippingMethod::register();

        config()->set('statamic.cargo.shipping.methods', ['fake_shipping_method' => []]);

        $order = Order::make()
            ->set('shipping_method', 'fake_shipping_method')
            ->set('shipping_option', [
                'name' => 'Standard Shipping',
                'handle' => 'standard_shipping',
                'price' => 500,
            ]);

        $this->assertInstanceOf(ShippingOption::class, $order->shippingOption());
        $this->assertEquals('Standard Shipping', $order->shippingOption()->name());
        $this->assertEquals(500, $order->shippingOption()->price());
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
