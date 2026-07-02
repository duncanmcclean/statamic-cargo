<?php

namespace Tests\Cart;

use DuncanMcClean\Cargo\Customers\GuestCustomer;
use DuncanMcClean\Cargo\Facades\Cart;
use DuncanMcClean\Cargo\Facades\Discount;
use Illuminate\Foundation\Http\FormRequest;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Collection;
use Statamic\Facades\Entry;
use Statamic\Facades\User;
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
                'shipping_address' => [
                    'line_1' => '123 ShippingMethod St',
                    'line_2' => 'Apt 1',
                    'city' => 'Shippingville',
                    'postcode' => '12345',
                    'country' => 'US',
                ],

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

        $shippingAddress = $cart->shippingAddress();
        $this->assertEquals('123 ShippingMethod St', $shippingAddress->line_1);
        $this->assertEquals('Apt 1', $shippingAddress->line_2);
        $this->assertEquals('Shippingville', $shippingAddress->city);
        $this->assertEquals('12345', $shippingAddress->postcode);
        $this->assertEquals('US', $shippingAddress->country);

        // Ensure that keys NOT in the order blueprint aren't saved.
        $this->assertNull($cart->get('random_field'));

        // Ensure that the grand total passed in the request didn't update the cart.
        $this->assertEquals(900, $cart->grandTotal());
    }

    #[Test]
    public function it_redirects_when_updating_the_cart()
    {
        $this->makeCart();

        $this
            ->from('/cart')
            ->patch('/!/cargo/cart', ['_redirect' => '/thank-you'])
            ->assertRedirect('/thank-you');
    }

    #[Test]
    public function it_doesnt_redirect_to_external_urls_when_updating_the_cart()
    {
        $this->makeCart();

        $this
            ->from('/cart')
            ->patch('/!/cargo/cart', ['_redirect' => 'https://evil.com/path'])
            ->assertRedirect('/cart');
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
    public function it_assigns_logged_in_user_to_cart_by_default()
    {
        config()->set('statamic.cargo.carts.always_checkout_as_guest', false);

        $cart = $this->makeCart();
        $cart->customer(null)->save();

        $user = User::make()->email('user@example.com')->save();

        $this->actingAs($user)
            ->from('/cart')
            ->patch('/!/cargo/cart', [
                'customer' => [
                    'name' => 'John Doe',
                    'email' => 'john@example.com',
                ],
            ])
            ->assertRedirect('/cart');

        $cart = $cart->fresh();

        $this->assertNotInstanceOf(GuestCustomer::class, $cart->customer());
        $this->assertEquals($user->id(), $cart->customer()->id());
    }

    #[Test]
    public function it_creates_guest_customer_when_always_checkout_as_guest_is_enabled()
    {
        config()->set('statamic.cargo.carts.always_checkout_as_guest', true);

        $cart = $this->makeCart();
        $cart->customer(null)->save();

        $user = User::make()->email('user@example.com')->save();

        $this->actingAs($user)
            ->from('/cart')
            ->patch('/!/cargo/cart', [
                'customer' => [
                    'name' => 'John Doe',
                    'email' => 'john@example.com',
                ],
            ])
            ->assertRedirect('/cart');

        $cart = $cart->fresh();

        $this->assertInstanceOf(GuestCustomer::class, $cart->customer());
        $this->assertEquals('John Doe', $cart->customer()->name());
        $this->assertEquals('john@example.com', $cart->customer()->email());
    }

    #[Test]
    public function it_does_not_update_user_data_when_always_checkout_as_guest_is_enabled()
    {
        config()->set('statamic.cargo.carts.always_checkout_as_guest', true);

        $cart = $this->makeCart();
        $cart->customer(null)->save();

        $user = User::make()
            ->email('original@example.com')
            ->set('name', 'Original Name')
            ->save();

        $this->actingAs($user)
            ->from('/cart')
            ->patch('/!/cargo/cart', [
                'customer' => [
                    'name' => 'New Name',
                    'email' => 'new@example.com',
                ],
            ])
            ->assertRedirect('/cart');

        $user = User::find($user->id());

        $this->assertEquals('original@example.com', $user->email());
        $this->assertEquals('Original Name', $user->get('name'));
    }

    #[Test]
    public function it_does_not_persist_protected_fields_to_the_logged_in_user()
    {
        config()->set('statamic.cargo.carts.always_checkout_as_guest', false);

        $cart = $this->makeCart();
        $cart->customer(null)->save();

        $user = User::make()->email('user@example.com')->save();
        $id = $user->id();

        $this->actingAs($user)
            ->from('/cart')
            ->patch('/!/cargo/cart', [
                'customer' => [
                    'name' => 'John Doe',
                    'company' => 'Acme Inc.',
                    'super' => true,
                    'roles' => ['admin'],
                    'password' => 'p4ssword',
                    'id' => 'hacked',
                ],
            ])
            ->assertRedirect('/cart');

        $user = User::find($id);

        $this->assertEquals('John Doe', $user->get('name'));
        $this->assertEquals('Acme Inc.', $user->get('company'));
        $this->assertEquals($id, $user->id());
        $this->assertFalse($user->isSuper());
        $this->assertEmpty($user->roles()->all());
        $this->assertNull($user->get('password'));
        $this->assertNull($user->get('password_hash'));
    }

    #[Test]
    public function it_does_not_persist_protected_fields_to_a_guest_customer()
    {
        config()->set('statamic.cargo.carts.always_checkout_as_guest', true);

        $cart = $this->makeCart();
        $cart->customer(null)->save();

        $this->from('/cart')
            ->patch('/!/cargo/cart', [
                'customer' => [
                    'name' => 'John Doe',
                    'email' => 'john@example.com',
                    'company' => 'Acme Inc.',
                    'super' => true,
                    'roles' => ['admin'],
                    'password' => 'p4ssword',
                    'id' => 'hacked',
                ],
            ])
            ->assertRedirect('/cart');

        $customer = $cart->fresh()->customer();

        $this->assertInstanceOf(GuestCustomer::class, $customer);
        $this->assertEquals('John Doe', $customer->name());
        $this->assertEquals('john@example.com', $customer->email());
        $this->assertEquals('Acme Inc.', $customer->get('company'));
        $this->assertNull($customer->get('super'));
        $this->assertNull($customer->get('roles'));
        $this->assertNull($customer->get('password'));
        $this->assertNotEquals('hacked', $customer->id());
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
    public function it_redirects_when_deleting_the_cart()
    {
        $this->makeCart();

        $this
            ->from('/cart')
            ->delete('/!/cargo/cart', ['_redirect' => '/'])
            ->assertRedirect('/');
    }

    #[Test]
    public function it_doesnt_redirect_to_external_urls_when_deleting_the_cart()
    {
        $this->makeCart();

        $this
            ->from('/cart')
            ->delete('/!/cargo/cart', ['_redirect' => 'https://evil.com/path'])
            ->assertRedirect('/cart');
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
