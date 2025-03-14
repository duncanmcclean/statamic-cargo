<?php

namespace Tests\Feature\Shipping;

use DuncanMcClean\Cargo\Cart\Calculator\ApplyShipping;
use DuncanMcClean\Cargo\Contracts\Cart\Cart as CartContract;
use DuncanMcClean\Cargo\Facades\Cart;
use DuncanMcClean\Cargo\Shipping\ShippingMethod;
use DuncanMcClean\Cargo\Shipping\ShippingOption;
use Illuminate\Support\Collection;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CanApplyShippingTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        PaidShipping::register();
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

class PaidShipping extends ShippingMethod
{
    public function options(CartContract $cart): Collection
    {
        return collect([
            ShippingOption::make($this)
                ->name('The Only Option')
                ->price(500),
        ]);
    }
}
