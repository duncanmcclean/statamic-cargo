<?php

namespace Tests;

use DuncanMcClean\Cargo\Contracts\Orders\Order;
use DuncanMcClean\Cargo\Events\DiscountRedeemed;
use DuncanMcClean\Cargo\Events\ProductNoStockRemaining;
use DuncanMcClean\Cargo\Events\ProductStockLow;
use DuncanMcClean\Cargo\Facades;
use DuncanMcClean\Cargo\Facades\Cart;
use DuncanMcClean\Cargo\Facades\Discount;
use DuncanMcClean\Cargo\Orders\OrderStatus;
use DuncanMcClean\Cargo\Payments\Gateways\PaymentGateway;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Event;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Collection;
use Statamic\Facades\Entry;
use Statamic\Testing\Concerns\PreventsSavingStacheItemsToDisk;

class CheckoutTest extends TestCase
{
    use PreventsSavingStacheItemsToDisk;

    protected function setUp(): void
    {
        parent::setUp();

        Cart::forgetCurrentCart();

        FakePaymentGateway::register();
        config()->set('statamic.cargo.payments.gateways', ['fake' => []]);
    }

    #[Test]
    public function can_checkout()
    {
        $cart = $this->makeCart();

        $this
            ->get('/!/cargo/payments/fake/checkout')
            ->assertRedirect();

        $this->assertNotNull($order = Facades\Order::query()->where('cart', $cart->id())->first());
        $this->assertEquals(OrderStatus::PaymentReceived, $order->status());
    }

    #[Test]
    public function can_checkout_a_free_cart()
    {
        $cart = $this->makeCart();

        Entry::make()->collection('products')->id('product-2')->data(['price' => 0])->save();
        $cart->lineItems([['product' => 'product-2', 'total' => 0, 'quantity' => 1]])->save();

        $this
            ->get('/!/cargo/cart/checkout')
            ->assertRedirect();

        $this->assertNotNull($order = Facades\Order::query()->where('cart', $cart->id())->first());
        $this->assertEquals(OrderStatus::PaymentReceived, $order->status());
        $this->assertEquals(0, $order->grandTotal());
    }

    #[Test]
    public function cant_checkout_with_invalid_payment_gateway()
    {
        $cart = $this->makeCart();

        $this
            ->get('/!/cargo/payments/invalid/checkout')
            ->assertNotFound();

        $this->assertNull(Facades\Order::query()->where('cart', $cart->id())->first());
    }

    #[Test]
    public function cant_checkout_without_customer_information()
    {
        $cart = $this->makeCart();
        $cart->customer(null)->save();

        $this
            ->get('/!/cargo/payments/fake/checkout')
            ->assertSessionHasErrors(['checkout' => 'Order cannot be created without customer information.']);

        $this->assertNull(Facades\Order::query()->where('cart', $cart->id())->first());
    }

    #[Test]
    public function cant_checkout_without_taxable_address()
    {
        $cart = $this->makeCart();
        $cart->remove('shipping_line_1')->save();

        $this
            ->get('/!/cargo/payments/fake/checkout')
            ->assertSessionHasErrors(['checkout' => 'Order cannot be created without an address.']);

        $this->assertNull(Facades\Order::query()->where('cart', $cart->id())->first());
    }

    #[Test]
    public function cant_checkout_when_stock_is_unavilable()
    {
        $cart = $this->makeCart();
        $cart->lineItems()->first()->product()->set('stock', 0)->save();

        $this
            ->get('/!/cargo/payments/fake/checkout')
            ->assertSessionHasErrors(['checkout' => 'One or more items in your cart are no longer available.']);

        $this->assertNull(Facades\Order::query()->where('cart', $cart->id())->first());
    }

    #[Test]
    public function ensure_product_stock_field_is_updated()
    {
        Event::fake();

        config()->set('statamic.cargo.products.low_stock_threshold', 10);

        $cart = $this->makeCart();
        $cart->lineItems()->update(123, ['quantity' => 2]);
        $cart->save();

        $product = Entry::find('product-1');
        $product->set('stock', 10);
        $product->save();

        $this
            ->get('/!/cargo/payments/fake/checkout')
            ->assertRedirect();

        $this->assertEquals(8, $product->fresh()->get('stock'));

        Event::assertDispatched(ProductStockLow::class);
    }

    #[Test]
    public function ensure_product_variant_stock_field_is_updated()
    {
        Event::fake();

        config()->set('statamic.cargo.products.low_stock_threshold', 10);

        $cart = $this->makeCart();
        $cart->lineItems()->update(123, ['quantity' => 2, 'variant' => 'Red']);
        $cart->save();

        $product = Entry::find('product-1');
        $product->set('product_variants', [
            'variants' => [['name' => 'Colour', 'values' => ['Red']]],
            'options' => [['key' => 'Red', 'variant' => 'Red', 'price' => 2550, 'stock' => 10]],
        ]);
        $product->save();

        $product->blueprint()->ensureField('product_variants', [
            'type' => 'product_variants',
            'option_fields' => [
                [
                    'handle' => 'stock',
                    'field' => ['type' => 'integer'],
                ],
            ],
        ])->save();

        $this
            ->get('/!/cargo/payments/fake/checkout')
            ->assertRedirect();

        $productVariant = Arr::get($product->fresh()->get('product_variants'), 'options.0');

        $this->assertEquals(8, $productVariant['stock']);

        Event::assertDispatched(ProductStockLow::class);
    }

    #[Test]
    public function it_dispatches_no_stock_remaining_event_when_stock_is_zero()
    {
        Event::fake();

        $cart = $this->makeCart();
        $cart->lineItems()->update(123, ['quantity' => 2]);
        $cart->save();

        $product = Entry::find('product-1');
        $product->set('stock', 2);
        $product->save();

        $this
            ->get('/!/cargo/payments/fake/checkout')
            ->assertRedirect();

        $this->assertEquals(0, $product->fresh()->get('stock'));

        Event::assertDispatched(ProductStockLow::class);
        Event::assertDispatched(ProductNoStockRemaining::class);
    }

    #[Test]
    public function discount_redeemed_event_is_dispatched()
    {
        Event::fake();

        Discount::make()->id('a')->type('percentage_off')->set('percentage_off', 50)->save();
        Discount::make()->id('b')->set('discount_code', 'B')->type('amount_off')->set('amount_off', 100)->save();

        $cart = $this->makeCart(['discount_code' => 'B']);

        $this->withoutExceptionHandling();

        $this
            ->get('/!/cargo/payments/fake/checkout')
            ->assertRedirect();

        $this->assertNotNull($order = Facades\Order::query()->where('cart', $cart->id())->first());
        $this->assertEquals(OrderStatus::PaymentReceived, $order->status());

        Event::assertDispatched(DiscountRedeemed::class, fn ($event) => $event->discount->id() === 'a');
        Event::assertDispatched(DiscountRedeemed::class, fn ($event) => $event->discount->id() === 'b');
    }

    private function makeCart(array $data = [])
    {
        $collection = tap(Collection::make('products'))->save();
        Entry::make()->collection('products')->id('product-1')->data(['price' => 5000])->save();

        $collection->entryBlueprint()->ensureField('stock', [
            'type' => 'integer',
        ])->save();

        $cart = Cart::make()
            ->customer(['name' => 'John Doe', 'email' => 'john.doe@example.com'])
            ->lineItems([['id' => '123', 'product' => 'product-1', 'total' => 5000, 'quantity' => 1]])
            ->merge([
                'shipping_line_1' => '123 Fake St',
                'shipping_city' => 'Fakeville',
                'shipping_postcode' => 'FA 1234',
                'shipping_country' => 'GBR',
                'shipping_state' => 'GLG',
                ...$data,
            ]);

        $cart->save();

        Cart::setCurrent($cart);

        return $cart;
    }
}

class FakePaymentGateway extends PaymentGateway
{
    public static $handle = 'fake';

    public function setup(\DuncanMcClean\Cargo\Contracts\Cart\Cart $cart): array
    {
        //

        return [];
    }

    public function process(Order $order): void
    {
        // Normally, this would be updated in the webhook, but for the sake of demonstration, we'll just update it here.
        $order->status(OrderStatus::PaymentReceived)->save();
    }

    public function capture(Order $order): void
    {
        //
    }

    public function cancel(\DuncanMcClean\Cargo\Contracts\Cart\Cart $cart): void
    {
        //
    }

    public function webhook(Request $request): Response
    {
        //

        return response();
    }

    public function refund(Order $order, int $amount): void
    {
        //
    }
}
