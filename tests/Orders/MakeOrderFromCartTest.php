<?php

namespace Tests\Orders;

use DuncanMcClean\Cargo\Facades\Cart;
use DuncanMcClean\Cargo\Facades\Order;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Collection;
use Statamic\Facades\Entry;
use Statamic\Facades\User;
use Statamic\Testing\Concerns\PreventsSavingStacheItemsToDisk;
use Tests\TestCase;

class MakeOrderFromCartTest extends TestCase
{
    use PreventsSavingStacheItemsToDisk;

    protected function setUp(): void
    {
        parent::setUp();

        Collection::make('products')->save();
    }

    #[Test]
    public function it_can_make_an_order_from_a_cart()
    {
        $product = tap(Entry::make()->collection('products')->set('price', 2500))->save();

        $cart = Cart::make()
            ->id('123')
            ->customer([
                'name' => 'John Doe',
                'email' => 'john.doe@example.com',
            ])
            ->lineItems([
                ['id' => '1', 'product' => $product->id(), 'quantity' => 2, 'unit_price' => 1000, 'total' => 2000],
            ])
            ->grandTotal(2500)
            ->subTotal(2000)
            ->discountTotal(100)
            ->taxTotal(400)
            ->shippingTotal(200)
            ->data([
                'shipping_method' => 'free_shipping',
                'shipping_option' => 'free_shipping',
                'shuffling' => 'is fun',
            ]);

        $order = Order::makeFromCart($cart);

        $this->assertEquals([
            'cart' => '123',
            'customer' => [
                'name' => 'John Doe',
                'email' => 'john.doe@example.com',
            ],
            'line_items' => [
                ['id' => '1', 'product' => $product->id(), 'quantity' => 2, 'unit_price' => 1000, 'total' => 2000],
            ],
            'shipping_method' => 'free_shipping',
            'grand_total' => 2500,
            'sub_total' => 2000,
            'discount_total' => 100,
            'tax_total' => 400,
            'shipping_total' => 200,
            'shipping_option' => 'free_shipping',
            'shuffling' => 'is fun',
            'status' => 'payment_pending',
        ], $order->fileData());
    }

    #[Test]
    public function it_stores_the_product_title_and_variant_name_on_line_items()
    {
        $product = tap(Entry::make()->collection('products')->data(['title' => 'T-shirt', 'price' => 1500]))->save();

        $variantProduct = tap(Entry::make()->collection('products')->data([
            'title' => 'Hoodie',
            'product_variants' => [
                'variants' => [['name' => 'Size', 'values' => ['Small']]],
                'options' => [['key' => 'Small', 'variant' => 'Small', 'price' => 3000]],
            ],
        ]))->save();

        $cart = Cart::make()->lineItems([
            ['id' => '1', 'product' => $product->id(), 'quantity' => 1],
            ['id' => '2', 'product' => $variantProduct->id(), 'variant' => 'Small', 'quantity' => 1],
        ]);

        $order = Order::makeFromCart($cart);

        $this->assertEquals('T-shirt', $order->lineItems()->find('1')->get('product_title'));
        $this->assertNull($order->lineItems()->find('1')->get('variant_name'));

        $this->assertEquals('Hoodie', $order->lineItems()->find('2')->get('product_title'));
        $this->assertEquals('Small', $order->lineItems()->find('2')->get('variant_name'));

        $this->assertNull($cart->lineItems()->find('2')->get('product_title'));
        $this->assertNull($cart->lineItems()->find('2')->get('variant_name'));
    }

    #[Test]
    public function it_adds_the_new_customer_flag_when_its_the_customers_first_order()
    {
        $user = User::make()->id('user-123')->email('first.time@example.com')->save();

        $cart = Cart::make()->customer($user);

        $order = Order::makeFromCart($cart);

        $this->assertEquals('user-123', $order->customer()->id());
        $this->assertEquals(true, $order->get('new_customer'));
    }

    #[Test]
    public function it_doesnt_add_the_new_customer_flag_when_the_customer_has_past_orders()
    {
        $user = User::make()->id('user-123')->email('first.time@example.com')->save();
        Order::make()->customer($user)->save();

        $cart = Cart::make()->customer($user);

        $order = Order::makeFromCart($cart);

        $this->assertEquals('user-123', $order->customer()->id());
        $this->assertNull($order->get('new_customer'));
    }
}
