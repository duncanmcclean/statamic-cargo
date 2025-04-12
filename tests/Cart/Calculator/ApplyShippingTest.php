<?php

namespace Tests\Cart\Calculator;

use DuncanMcClean\Cargo\Cart\Calculator\ApplyShipping;
use DuncanMcClean\Cargo\Facades\Cart;
use PHPUnit\Framework\Attributes\Test;
use Tests\Fixtures\ShippingMethods\PaidShipping;
use Tests\TestCase;

class ApplyShippingTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        PaidShipping::register();

        config()->set('statamic.cargo.shipping.methods', ['paid_shipping' => []]);
    }

    #[Test]
    public function applies_shipping_cost_to_cart()
    {
        $cart = Cart::make()->data([
            'shipping_method' => 'paid_shipping',
            'shipping_option' => 'the_only_option',
        ]);

        $cart = app(ApplyShipping::class)->handle($cart, fn ($cart) => $cart);

        $this->assertEquals(500, $cart->shippingTotal());
    }

    #[Test]
    public function doesnt_apply_shipping_cost_when_cart_is_missing_a_shipping_method()
    {
        $cart = Cart::make();

        $cart = app(ApplyShipping::class)->handle($cart, fn ($cart) => $cart);

        $this->assertEquals(0, $cart->shippingTotal());
    }

    #[Test]
    public function removes_shipping_keys_when_shipping_option_is_no_longer_available()
    {
        $cart = Cart::make()->data([
            'shipping_method' => 'paid_shipping',
            'shipping_option' => 'a_non_existent_option',
        ]);

        $cart = app(ApplyShipping::class)->handle($cart, fn ($cart) => $cart);

        $this->assertNull($cart->shippingMethod());
        $this->assertFalse($cart->has('shipping_option'));

        $this->assertEquals(0, $cart->shippingTotal());
    }
}
