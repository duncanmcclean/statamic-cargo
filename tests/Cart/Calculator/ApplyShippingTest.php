<?php

namespace Tests\Cart\Calculator;

use DuncanMcClean\Cargo\Cart\Calculator\ApplyShipping;
use DuncanMcClean\Cargo\Facades\Cart;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Collection;
use Statamic\Facades\Entry;
use Statamic\Testing\Concerns\PreventsSavingStacheItemsToDisk;
use Tests\Fixtures\ShippingMethods\DynamicShipping;
use Tests\Fixtures\ShippingMethods\PaidShipping;
use Tests\TestCase;

class ApplyShippingTest extends TestCase
{
    use PreventsSavingStacheItemsToDisk;

    protected function setUp(): void
    {
        parent::setUp();

        PaidShipping::register();
        DynamicShipping::register();

        config()->set('statamic.cargo.shipping.methods', [
            'paid_shipping' => [],
            'dynamic_shipping' => [],
        ]);
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

    #[Test]
    public function reresolves_shipping_price_when_option_is_already_saved_to_the_cart()
    {
        // ApplyShipping persists the selected option (with its price) on every calculation.
        // On a later recalculation, after the cart contents have changed, the price must
        // be resolved from the shipping method to avoid stale prices.
        Collection::make('products')->save();
        $product = tap(Entry::make()->collection('products')->data(['price' => 1000]))->save();

        $cart = Cart::make()->data([
            'shipping_method' => 'dynamic_shipping',
            'shipping_option' => [
                'name' => 'Dynamic Option',
                'handle' => 'dynamic_option',
                'price' => 100,
                'accepts_payment_on_delivery' => false,
            ],
        ]);

        $cart->lineItems([
            ['id' => 'a', 'product' => $product->id(), 'quantity' => 1],
            ['id' => 'b', 'product' => $product->id(), 'quantity' => 1],
            ['id' => 'c', 'product' => $product->id(), 'quantity' => 1],
        ]);

        $cart = app(ApplyShipping::class)->handle($cart, fn ($cart) => $cart);

        $this->assertEquals(300, $cart->shippingTotal());
        $this->assertEquals(300, $cart->get('shipping_option')['price']);
    }
}
