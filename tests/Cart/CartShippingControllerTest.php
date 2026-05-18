<?php

namespace Tests\Cart;

use DuncanMcClean\Cargo\Facades\Cart;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Collection;
use Statamic\Facades\Entry;
use Statamic\Testing\Concerns\PreventsSavingStacheItemsToDisk;
use Tests\TestCase;

class CartShippingControllerTest extends TestCase
{
    use PreventsSavingStacheItemsToDisk;

    protected function setUp(): void
    {
        parent::setUp();

        Cart::forgetCurrentCart();
    }

    #[Test]
    public function it_throws_a_not_found_exception_when_no_current_cart_is_set()
    {
        $this
            ->getJson('/!/cargo/cart/shipping')
            ->assertNotFound();
    }

    #[Test]
    public function it_returns_a_validation_error_when_the_cart_has_no_shipping_address()
    {
        $this->makeCart();

        $this
            ->getJson('/!/cargo/cart/shipping')
            ->assertStatus(422)
            ->assertJsonValidationErrors([
                'shipping_address' => 'Shipping address is missing from the cart.',
            ]);
    }

    #[Test]
    public function it_returns_a_validation_error_when_the_cart_has_no_physical_products()
    {
        $cart = $this->makeCart(productType: 'digital');
        $cart->merge(['shipping_address' => ['country' => 'US']]);
        $cart->save();

        $this
            ->getJson('/!/cargo/cart/shipping')
            ->assertStatus(422)
            ->assertJsonValidationErrors([
                'line_items' => 'This cart does not contain any physical products.',
            ]);
    }

    protected function makeCart(string $productType = 'physical')
    {
        Collection::make('products')->save();
        Entry::make()
            ->collection('products')
            ->id('product-1')
            ->data(['title' => 'Product 1', 'price' => 1000, 'type' => $productType])
            ->save();

        $cart = Cart::make()
            ->customer(['name' => 'John Doe', 'email' => 'john.doe@example.com'])
            ->lineItems([
                [
                    'product' => 'product-1',
                    'quantity' => 1,
                    'total' => 1000,
                ],
            ]);

        $cart->save();

        Cart::setCurrent($cart);

        return $cart;
    }
}
