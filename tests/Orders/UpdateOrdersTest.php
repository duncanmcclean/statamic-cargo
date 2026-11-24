<?php

namespace Tests\Orders;

use DuncanMcClean\Cargo\Events\OrderRefunded;
use DuncanMcClean\Cargo\Facades\Order;
use DuncanMcClean\Cargo\Orders\OrderStatus;
use Illuminate\Support\Facades\Event;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Collection;
use Statamic\Facades\Role;
use Statamic\Facades\User;
use Statamic\Testing\Concerns\PreventsSavingStacheItemsToDisk;
use Tests\TestCase;

class UpdateOrdersTest extends TestCase
{
    use PreventsSavingStacheItemsToDisk;

    protected function setUp(): void
    {
        parent::setUp();

        Collection::make('products')->save();
    }

    #[Test]
    public function can_update_order()
    {
        $order = tap(Order::make()->orderNumber(1002))->save();

        $this
            ->actingAs(User::make()->makeSuper()->save())
            ->patch(cp_route('cargo.orders.update', $order->id()), [
                'shipping_address' => [
                    'line_1' => '123 Fake Street',
                    'city' => 'Fakeville',
                    'postcode' => 'FA1 1KE',
                    'country' => 'United Kingdom',
                ],
                'status' => 'shipped',
                'grand_total' => 1000, // This should be ignored.
            ])
            ->assertOk()
            ->assertSee('Order #1002');

        $order = $order->fresh();

        $shippingAddress = $order->shippingAddress();
        $this->assertEquals($shippingAddress->line_1, '123 Fake Street');
        $this->assertEquals($shippingAddress->city, 'Fakeville');
        $this->assertEquals($shippingAddress->postcode, 'FA1 1KE');
        $this->assertEquals($shippingAddress->country, 'United Kingdom');
        $this->assertEquals($order->status(), OrderStatus::Shipped);
        $this->assertEquals($order->grandTotal(), 0);
    }

    #[Test]
    public function can_refund_order_when_cancelling_it()
    {
        Event::fake([OrderRefunded::class]);

        $order = Order::make()
            ->status(OrderStatus::PaymentReceived)
            ->grandTotal(2500)
            ->data(['amount_refunded' => 1000, 'payment_gateway' => 'dummy']);

        $order->save();

        $this
            ->actingAs(User::make()->makeSuper()->save())
            ->patch(cp_route('cargo.orders.update', $order->id()), [
                'status' => 'cancelled',
                'payment_gateway' => 'dummy',
                'refund' => true,
            ])
            ->assertOk();

        $order = $order->fresh();

        $this->assertEquals(OrderStatus::Cancelled, $order->status());

        Event::assertDispatched(OrderRefunded::class, fn ($event) => $event->amount === 1500);
    }

    #[Test]
    public function doesnt_refund_order_when_it_was_already_cancelled()
    {
        Event::fake([OrderRefunded::class]);

        $order = Order::make()
            ->status(OrderStatus::Cancelled)
            ->grandTotal(2500)
            ->data(['amount_refunded' => 0, 'payment_gateway' => 'dummy']);

        $order->save();

        $this
            ->actingAs(User::make()->makeSuper()->save())
            ->patch(cp_route('cargo.orders.update', $order->id()), [
                'status' => 'cancelled',
                'payment_gateway' => 'dummy',
                'refund' => true,
            ])
            ->assertOk();

        $this->assertEquals(0, $order->fresh()->get('amount_refunded'));

        Event::assertNotDispatched(OrderRefunded::class);
    }

    #[Test]
    public function cant_update_order_without_permissions()
    {
        $order = tap(Order::make())->save();

        Role::make('test')->addPermission('access cp')->save();

        $this
            ->actingAs(User::make()->assignRole('test')->save())
            ->patch(cp_route('cargo.orders.update', $order->id()))
            ->assertRedirect('/cp');
    }
}
