<?php

namespace Payments;

use DuncanMcClean\Cargo\Contracts\Orders\Order as OrderContract;
use DuncanMcClean\Cargo\Facades\Cart;
use DuncanMcClean\Cargo\Facades\Order;
use DuncanMcClean\Cargo\Orders\OrderStatus;
use DuncanMcClean\Cargo\Payments\Gateways\Dummy;
use DuncanMcClean\Cargo\Payments\Gateways\PayOnDelivery;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Collection;
use Statamic\Facades\Entry;
use Tests\Fixtures\ShippingMethods\FakeShippingMethod;
use Tests\TestCase;

#[Group('payments')]
class PayOnDeliveryTest extends TestCase
{
    #[Test]
    public function it_can_determine_availability()
    {
        FakeShippingMethod::register();
        config()->set('statamic.cargo.shipping.methods', ['fake_shipping_method' => []]);

        $cart = $this->makeCartWithGuestCustomer();

        // No shipping option selected.
        $this->assertFalse((new PayOnDelivery)->isAvailable($cart));

        // Shipping option selected, doesn't accept payment on delivery.
        $cart
            ->set('shipping_method', 'fake_shipping_method')
            ->set('shipping_option', 'standard_shipping');

        $this->assertFalse((new PayOnDelivery)->isAvailable($cart));

        // Shipping option selected, accepts payment on delivery.
        $cart
            ->set('shipping_method', 'fake_shipping_method')
            ->set('shipping_option', 'pay_on_delivery');

        $this->assertTrue((new PayOnDelivery)->isAvailable($cart));
    }

    #[Test]
    public function it_can_refund_an_order()
    {
        $order = $this->makeOrder();

        (new Dummy)->refund($order, 500);

        $this->assertEquals(500, $order->fresh()->get('amount_refunded'));
    }

    private function makeOrder(): OrderContract
    {
        $order = Order::make()
            ->status(OrderStatus::PaymentPending)
            ->grandTotal(1000)
            ->lineItems([
                ['product' => 'product-id', 'quantity' => 1, 'total' => 1000],
            ]);

        $order->save();

        return $order;
    }

    private function makeCartWithGuestCustomer()
    {
        Collection::make('products')->save();
        Entry::make()->id('product-id')->collection('products')->data(['price' => 1000])->save();

        $cart = Cart::make()
            ->lineItems([
                ['product' => 'product-id', 'quantity' => 1, 'total' => 1000],
            ])
            ->customer(['name' => 'David Hasselhoff', 'email' => 'david@hasselhoff.com']);

        $cart->save();

        return $cart;
    }
}
