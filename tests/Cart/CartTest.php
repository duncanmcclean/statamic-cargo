<?php

namespace Tests\Cart;

use DuncanMcClean\Cargo\Contracts\Cart\Cart as CartContract;
use DuncanMcClean\Cargo\Events\CartCreated;
use DuncanMcClean\Cargo\Events\CartDeleted;
use DuncanMcClean\Cargo\Events\CartSaved;
use DuncanMcClean\Cargo\Facades\Cart;
use DuncanMcClean\Cargo\Facades\Discount;
use DuncanMcClean\Cargo\Facades\TaxClass;
use DuncanMcClean\Cargo\Facades\TaxZone;
use DuncanMcClean\Cargo\Shipping\ShippingOption;
use Illuminate\Support\Facades\Event;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Collection;
use Statamic\Facades\Entry;
use Statamic\Testing\Concerns\PreventsSavingStacheItemsToDisk;
use Tests\Fixtures\ShippingMethods\FakeShippingMethod;
use Tests\TestCase;

class CartTest extends TestCase
{
    use PreventsSavingStacheItemsToDisk;

    #[Test]
    public function recalculates_totals_when_saving()
    {
        Collection::make('products')->save();
        Entry::make()->id('product-id')->collection('products')->data(['price' => 500])->save();

        // The totals here are wrong. They'll get fixed when the totals are recalculated.
        $cart = Cart::make()
            ->grandTotal(2000)
            ->subtotal(2000)
            ->discountTotal(500)
            ->lineItems([
                ['product' => 'product-id', 'quantity' => 2, 'total' => 2000],
            ]);

        $cart->save();

        $this->assertEquals(1000, $cart->grandTotal());
        $this->assertEquals(1000, $cart->subtotal());
        $this->assertEquals(0, $cart->discountTotal());
        $this->assertEquals(1000, $cart->lineItems()->first()->total());
    }

    #[Test]
    public function does_not_recalculate_totals_when_nothing_has_changed()
    {
        Collection::make('products')->save();
        Entry::make()->id('product-id')->collection('products')->data(['price' => 500])->save();

        $cart = Cart::make()
            ->grandTotal(1000)
            ->subtotal(1000)
            ->lineItems([
                ['product' => 'product-id', 'quantity' => 2, 'total' => 1000],
            ]);

        $cart->set('fingerprint', $fingerprint = $cart->fingerprint());

        $cart = \Mockery::mock($cart)->makePartial();
        $cart->shouldNotReceive('recalculate');

        $cart->save();

        $this->assertEquals(1000, $cart->grandTotal());
        $this->assertEquals(1000, $cart->subtotal());
        $this->assertEquals(0, $cart->discountTotal());
        $this->assertEquals(1000, $cart->lineItems()->first()->total());
        $this->assertEquals($fingerprint, $cart->get('fingerprint'));
    }

    #[Test]
    public function does_not_recalculate_totals_when_recalculating_is_disabled()
    {
        Collection::make('products')->save();
        Entry::make()->id('product-id')->collection('products')->data(['price' => 500])->save();

        // The totals here are wrong. They'll get fixed when the totals are recalculated.
        $cart = Cart::make()
            ->grandTotal(2000)
            ->subtotal(2000)
            ->discountTotal(500)
            ->lineItems([
                ['product' => 'product-id', 'quantity' => 2, 'total' => 2000],
            ]);

        $cart->saveWithoutRecalculating();

        $this->assertEquals(2000, $cart->grandTotal());
        $this->assertEquals(2000, $cart->subtotal());
        $this->assertEquals(500, $cart->discountTotal());
        $this->assertEquals(2000, $cart->lineItems()->first()->total());
    }

    #[Test]
    public function it_returns_the_tax_breakdown()
    {
        TaxClass::make()->handle('standard')->set('name', 'Standard')->save();
        TaxClass::make()->handle('reduced')->set('name', 'Reduced')->save();

        TaxZone::make()->handle('uk_vat')->data([
            'name' => 'UK VAT',
            'type' => 'countries',
            'countries' => ['GBR'],
            'rates' => ['standard' => 20, 'reduced' => 5],
        ])->save();

        TaxZone::make()->handle('gls_vat')->data([
            'name' => 'Glasgow VAT',
            'type' => 'states',
            'countries' => ['GBR'],
            'states' => ['GLS'],
            'rates' => ['reduced' => 4],
        ])->save();

        Collection::make('products')->save();

        $productA = Entry::make()->collection('products')->data(['price' => 10000, 'tax_class' => 'standard']);
        $productA->save();

        $productB = Entry::make()->collection('products')->data(['price' => 500, 'tax_class' => 'standard']);
        $productB->save();

        $productC = Entry::make()->collection('products')->data(['price' => 500, 'tax_class' => 'reduced']);
        $productC->save();

        $cart = Cart::make()
            ->lineItems([
                [
                    'id' => 'one',
                    'product' => $productA->id(),
                    'quantity' => 1,
                    'total' => 10000,
                    'tax_breakdown' => [
                        ['rate' => 20, 'description' => 'Standard', 'zone' => 'UK VAT', 'amount' => 2000],
                    ],
                ],
                [
                    'id' => 'two',
                    'product' => $productB->id(),
                    'quantity' => 1,
                    'total' => 500,
                    'tax_breakdown' => [
                        ['rate' => 20, 'description' => 'Standard', 'zone' => 'UK VAT', 'amount' => 100],
                    ],
                ],
                [
                    'id' => 'three',
                    'product' => $productC->id(),
                    'quantity' => 1,
                    'total' => 500,
                    'tax_breakdown' => [
                        ['rate' => 5, 'description' => 'Reduced', 'zone' => 'UK VAT', 'amount' => 25],
                        ['rate' => 4, 'description' => 'Reduced', 'zone' => 'Glasgow VAT', 'amount' => 20],
                    ],
                ],
            ])
            ->subtotal(10500)
            ->shippingTotal(2000)
            ->data([
                'shipping_line_1' => '123 Fake St',
                'shipping_city' => 'Glasgow',
                'shipping_postcode' => 'G1 234',
                'shipping_country' => 'GBR',
                'shipping_state' => 'GLS',
                'shipping_method' => 'paid_shipping',
                'shipping_option' => 'the_only_option',
                'shipping_tax_breakdown' => [
                    ['rate' => 20, 'description' => 'Standard', 'zone' => 'UK VAT', 'amount' => 400],
                ],
            ]);

        $this->assertEquals([
            ['rate' => 20, 'description' => 'Standard', 'zone' => 'UK VAT', 'amount' => 2500],
            ['rate' => 5, 'description' => 'Reduced', 'zone' => 'UK VAT', 'amount' => 25],
            ['rate' => 4, 'description' => 'Reduced', 'zone' => 'Glasgow VAT', 'amount' => 20],
        ], $cart->taxBreakdown());
    }

    #[Test]
    public function it_returns_discounts()
    {
        $this->makeProduct('123')->set('price', 2500)->save();
        $this->makeProduct('456')->set('price', 5000)->save();

        $discountA = Discount::make()->id('a')->name('Discount A')->set('discount_code', 'A')->type('percentage_off')->set('percentage_off', 10); // Shouldn't be applied, it has a discount code.
        $discountB = Discount::make()->id('b')->name('Discount B')->type('percentage_off')->set('percentage_off', 15); // Should be applied to both line items.
        $discountC = Discount::make()->id('c')->name('Discount C')->type('amount_off')->set('amount_off', 100)->set('products', ['123']); // Should be applied to the first line item.
        $discountD = Discount::make()->id('d')->name('Discount D')->type('amount_off')->set('amount_off', 200)->set('products', ['456'])->set('end_date', '2025-01-01'); // Shouldn't be applied, it has expired.

        $discountA->save();
        $discountB->save();
        $discountC->save();
        $discountD->save();

        $cart = Cart::make()->lineItems([
            ['id' => 'abc', 'product' => '123', 'quantity' => 1, 'total' => 2500],
            ['id' => 'def', 'product' => '456', 'quantity' => 1, 'total' => 5000],
        ]);

        $cart->save();

        $this->assertEquals([
            ['discount' => 'b', 'description' => 'Discount B', 'amount' => 1125],
            ['discount' => 'c', 'description' => 'Discount C', 'amount' => 100],
        ], $cart->discounts());
    }

    #[Test]
    public function it_returns_the_shipping_method()
    {
        FakeShippingMethod::register();

        config()->set('statamic.cargo.shipping.methods', ['fake_shipping_method' => []]);

        $cart = Cart::make()->set('shipping_method', 'fake_shipping_method');

        $this->assertInstanceOf(FakeShippingMethod::class, $cart->shippingMethod());
    }

    #[Test]
    public function it_returns_the_shipping_option()
    {
        FakeShippingMethod::register();

        config()->set('statamic.cargo.shipping.methods', ['fake_shipping_method' => []]);

        $cart = Cart::make()
            ->set('shipping_method', 'fake_shipping_method')
            ->set('shipping_option', [
                'name' => 'Standard Shipping',
                'handle' => 'standard_shipping',
                'price' => 500,
            ]);

        $this->assertInstanceOf(ShippingOption::class, $cart->shippingOption());
        $this->assertEquals('Standard Shipping', $cart->shippingOption()->name());
        $this->assertEquals(500, $cart->shippingOption()->price());
    }

    #[Test]
    public function cart_can_be_saved()
    {
        Event::fake();

        $this->assertNull(Cart::find('abc'));

        $cart = Cart::make()->id('abc');

        $cart->save();

        $this->assertInstanceOf(CartContract::class, $cart = Cart::find('abc'));
        $this->assertEquals('abc', $cart->id());
        $this->assertFileExists($cart->path());
        $this->assertStringContainsString('content/cargo/carts/abc.yaml', $cart->path());

        Event::assertDispatched(CartCreated::class, function ($event) use ($cart) {
            return $event->cart->id() === $cart->id();
        });

        Event::assertDispatched(CartSaved::class, function ($event) use ($cart) {
            return $event->cart->id() === $cart->id();
        });
    }

    #[Test]
    public function cart_can_be_saved_quietly()
    {
        Event::fake();

        $this->assertNull(Cart::find('abc'));

        $cart = Cart::make()->id('abc');

        $cart->saveQuietly();

        $this->assertInstanceOf(CartContract::class, $cart = Cart::find('abc'));
        $this->assertEquals('abc', $cart->id());
        $this->assertFileExists($cart->path());
        $this->assertStringContainsString('content/cargo/carts/abc.yaml', $cart->path());

        Event::assertNotDispatched(CartCreated::class, function ($event) use ($cart) {
            return $event->cart->id() === $cart->id();
        });

        Event::assertNotDispatched(CartSaved::class, function ($event) use ($cart) {
            return $event->cart->id() === $cart->id();
        });
    }

    #[Test]
    public function cart_can_be_deleted()
    {
        Event::fake();

        $cart = tap(Cart::make())->save();

        $this->assertFileExists($cart->path());

        $cart->delete();

        $this->assertFileDoesNotExist($cart->path());

        Event::assertDispatched(CartDeleted::class, function ($event) use ($cart) {
            return $event->cart->id() === $cart->id();
        });
    }

    #[Test]
    public function cart_can_be_deleted_quietly()
    {
        Event::fake();

        $cart = tap(Cart::make())->save();

        $this->assertFileExists($cart->path());

        $cart->deleteQuietly();

        $this->assertFileDoesNotExist($cart->path());

        Event::assertNotDispatched(CartDeleted::class, function ($event) use ($cart) {
            return $event->cart->id() === $cart->id();
        });
    }

    protected function makeProduct($id = null)
    {
        Collection::make('products')->save();

        return tap(Entry::make()->collection('products')->id($id))->save();
    }
}
