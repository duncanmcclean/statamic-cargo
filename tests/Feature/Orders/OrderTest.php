<?php

namespace Tests\Feature\Orders;

use DuncanMcClean\Cargo\Facades\Cart;
use DuncanMcClean\Cargo\Facades\Order;
use DuncanMcClean\Cargo\Orders\OrderStatus;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\User;
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

        $this->assertInstanceOf(\DuncanMcClean\Cargo\Contracts\Orders\Order::class, $order);

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
    public function can_query_columns()
    {
        Cart::make()->id('abc')->save();
        Cart::make()->id('def')->save();
        Cart::make()->id('ghi')->save();

        Order::make()->id('123')->cart('abc')->grandTotal(1150)->customer(['name' => 'Foo', 'email' => 'foo@example.com'])->save();
        Order::make()->id('456')->cart('def')->grandTotal(3470)->customer(['name' => 'Bar', 'email' => 'bar@example.com'])->save();
        Order::make()->id('789')->cart('ghi')->grandTotal(9500)->customer(['name' => 'Baz', 'email' => 'baz@example.com'])->save();

        $query = Order::query()->where('grand_total', '<', 5000)->get();

        $this->assertCount(2, $query);
        $this->assertEquals([123, 456], $query->map->id()->all());
    }

    #[Test]
    public function can_query_status()
    {
        Cart::make()->id('abc')->save();
        Cart::make()->id('def')->save();
        Cart::make()->id('ghi')->save();

        Order::make()->id('123')->cart('abc')->status(OrderStatus::PaymentPending)->customer(['name' => 'Foo', 'email' => 'foo@example.com'])->save();
        Order::make()->id('456')->cart('def')->status(OrderStatus::PaymentPending)->customer(['name' => 'Bar', 'email' => 'bar@example.com'])->save();
        Order::make()->id('789')->cart('ghi')->status(OrderStatus::Shipped)->customer(['name' => 'Baz', 'email' => 'baz@example.com'])->save();

        $query = Order::query()->whereStatus(OrderStatus::PaymentPending)->get();

        $this->assertCount(2, $query);
        $this->assertEquals([123, 456], $query->map->id()->all());
    }

    #[Test]
    public function can_query_customers()
    {
        Cart::make()->id('abc')->save();
        Cart::make()->id('def')->save();
        Cart::make()->id('ghi')->save();

        User::make()->id('foo')->email('foo@example.com')->save();

        Order::make()->id('123')->cart('abc')->grandTotal(1150)->customer('foo')->save();
        Order::make()->id('456')->cart('def')->grandTotal(3470)->customer(['name' => 'Bar', 'email' => 'bar@example.com'])->save();
        Order::make()->id('789')->cart('ghi')->grandTotal(9500)->customer('foo')->save();

        // Query users
        $query = Order::query()->where('customer', 'foo')->get();

        $this->assertCount(2, $query);
        $this->assertEquals([123, 789], $query->map->id()->all());

        // Query guest customers
        $query = Order::query()->where('customer', 'guest::bar@example.com')->get();

        $this->assertCount(1, $query);
        $this->assertEquals([456], $query->map->id()->all());
    }

    #[Test]
    public function can_query_data()
    {
        Cart::make()->id('abc')->save();
        Cart::make()->id('def')->save();
        Cart::make()->id('ghi')->save();

        Order::make()->id('123')->cart('abc')->customer(['name' => 'Foo', 'email' => 'foo@example.com'])->data(['foo' => true])->save();
        Order::make()->id('456')->cart('def')->customer(['name' => 'Bar', 'email' => 'bar@example.com'])->data(['foo' => false])->save();
        Order::make()->id('789')->cart('ghi')->customer(['name' => 'Baz', 'email' => 'baz@example.com'])->data(['foo' => true])->save();

        $query = Order::query()->where('foo', true)->get();

        $this->assertCount(2, $query);
        $this->assertEquals([123, 789], $query->map->id()->all());
    }
}
