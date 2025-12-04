<?php

namespace Tests\Cart;

use DuncanMcClean\Cargo\Customers\GuestCustomer;
use DuncanMcClean\Cargo\Facades\Cart;
use DuncanMcClean\Cargo\Facades\Discount;
use Illuminate\Foundation\Http\FormRequest;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Collection;
use Statamic\Facades\Entry;
use Statamic\Testing\Concerns\PreventsSavingStacheItemsToDisk;
use Tests\TestCase;

class CartControllerTest extends TestCase
{
    use PreventsSavingStacheItemsToDisk;

    protected function setUp(): void
    {
        parent::setUp();

        Cart::forgetCurrentCart();
    }

    #[Test]
    public function it_returns_the_current_cart()
    {
        $cart = $this->makeCart();

        $this
            ->get('/!/cargo/cart')
            ->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'customer',
                    'line_items' => [
                        [
                            'id',
                            'product' => ['id'],
                            'quantity',
                            'total',
                        ],
                    ],
                ],
            ])
            ->assertJsonPath('data.id', $cart->id());
    }

    #[Test]
    public function it_throws_a_not_found_exception_when_no_current_cart_is_set()
    {
        $this
            ->get('/!/cargo/cart')
            ->assertNotFound();
    }

    #[Test]
    public function it_updates_the_cart()
    {
        $cart = $this->makeCart();

        $discount = Discount::make()
            ->set('discount_code', 'FOOBAR')
            ->type('percentage_off')
            ->set('percentage_off', 10);

        $discount->save();

        $this
            ->from('/cart')
            ->patch('/!/cargo/cart', [
                'customer' => [
                    'name' => 'Jane Doe',
                    'email' => 'jane.doe@example.com',
                ],
                'discount_code' => 'FOOBAR',
                'shipping_line_1' => '123 ShippingMethod St',
                'shipping_line_2' => 'Apt 1',
                'shipping_city' => 'Shippingville',
                'shipping_postcode' => '12345',
                'shipping_country' => 'US',

                // These fields shouldn't get updated.
                'random_field' => 'foo',
                'grand_total' => 500,
            ])
            ->assertRedirect('/cart');

        $cart = $cart->fresh();

        $this->assertInstanceOf(GuestCustomer::class, $cart->customer());
        $this->assertEquals('Jane Doe', $cart->customer()->name());
        $this->assertEquals('jane.doe@example.com', $cart->customer()->email());

        $this->assertEquals('FOOBAR', $cart->get('discount_code'));

        $this->assertEquals('123 ShippingMethod St', $cart->get('shipping_line_1'));
        $this->assertEquals('Apt 1', $cart->get('shipping_line_2'));
        $this->assertEquals('Shippingville', $cart->get('shipping_city'));
        $this->assertEquals('12345', $cart->get('shipping_postcode'));
        $this->assertEquals('US', $cart->get('shipping_country'));

        // Ensure that keys NOT in the order blueprint aren't saved.
        $this->assertNull($cart->get('random_field'));

        // Ensure that the grand total passed in the request didn't update the cart.
        $this->assertEquals(900, $cart->grandTotal());
    }

    #[Test]
    public function it_updates_the_cart_and_expects_a_json_response()
    {
        $cart = $this->makeCart();

        $this
            ->patchJson('/!/cargo/cart')
            ->assertOk()
            ->assertJsonStructure(['data' => ['id', 'customer', 'line_items']])
            ->assertJsonPath('data.id', $cart->id());
    }

    #[Test]
    public function it_updates_the_cart_and_uses_custom_form_request()
    {
        $cart = $this->makeCart();

        $this
            ->from('/cart')
            ->patch('/!/cargo/cart', [
                '_request' => encrypt('Tests\Cart\CartFormRequest'),
                'foo' => 'bar',
            ])
            ->assertSessionHasErrors('baz');

        $cart = $cart->fresh();

        $this->assertNull($cart->get('foo'));
        $this->assertNull($cart->get('baz'));
    }

    #[Test]
    public function it_cant_add_invalid_discount_code()
    {
        $cart = tap($this->makeCart())->save();

        $this->assertNull($cart->get('discount_code'));

        $this
            ->from('/cart')
            ->patch('/!/cargo/cart', [
                'discount_code' => 'FOOBARZ',
            ])
            ->assertSessionHasErrors('discount_code');

        $this->assertNull($cart->fresh()->get('discount_code'));
    }

    #[Test]
    public function it_throws_validation_error_when_customer_email_is_invalid()
    {
        $cart = $this->makeCart();
        $cart->customer(null)->save();

        $this
            ->from('/cart')
            ->patch('/!/cargo/cart', [
                'customer' => ['email' => 'name@domain'],
            ])
            ->assertSessionHasErrors('customer.email');

        $this->assertNull($cart->fresh()->customer());
    }

    #[Test]
    public function it_deletes_the_cart()
    {
        $cart = $this->makeCart();

        $this
            ->from('/cart')
            ->delete('/!/cargo/cart')
            ->assertRedirect('/cart');

        $this->assertNull(Cart::find($cart->id()));
    }

    #[Test]
    public function it_deletes_the_cart_and_expects_a_json_response()
    {
        $cart = $this->makeCart();

        $this
            ->deleteJson('/!/cargo/cart')
            ->assertOk()
            ->assertJson([]);
    }

    protected function makeCart()
    {
        Collection::make('products')->save();
        Entry::make()->collection('products')->id('product-1')->data(['title' => 'Product 1', 'price' => 1000])->save();

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

class CartFormRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'baz' => 'required',
        ];
    }

    public function messages(): array
    {
        return [
            'bar.required' => 'The baz thingy should be here...',
        ];
    }
}
