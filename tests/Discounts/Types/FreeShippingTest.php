<?php

namespace Tests\Discounts\Types;

use DuncanMcClean\Cargo\Cart\Calculator\Calculator;
use DuncanMcClean\Cargo\Contracts\Discounts\Discount as DiscountContract;
use DuncanMcClean\Cargo\Facades\Cart;
use DuncanMcClean\Cargo\Facades\Discount;
use DuncanMcClean\Cargo\Facades\TaxClass;
use DuncanMcClean\Cargo\Facades\TaxZone;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Collection;
use Statamic\Facades\Entry;
use Statamic\Facades\User;
use Statamic\Testing\Concerns\PreventsSavingStacheItemsToDisk;
use Tests\Fixtures\ShippingMethods\PaidShipping;
use Tests\TestCase;

class FreeShippingTest extends TestCase
{
    use PreventsSavingStacheItemsToDisk;

    protected function setUp(): void
    {
        parent::setUp();

        PaidShipping::register();

        config()->set('statamic.cargo.shipping.methods', ['paid_shipping' => []]);

        Collection::make('products')->save();
        Entry::make()->id('product-id')->collection('products')->data(['price' => 2500, 'tax_class' => 'standard'])->save();
    }

    #[Test]
    public function it_discounts_shipping_when_a_shipping_option_is_selected()
    {
        Discount::make()->handle('free-shipping')->title('Free Shipping')->type('free_shipping')->save();

        $cart = Cart::make()
            ->lineItems([['id' => 'abc', 'product' => 'product-id', 'quantity' => 1]])
            ->data(['shipping_method' => 'paid_shipping', 'shipping_option' => 'the_only_option']);

        $cart = Calculator::calculate($cart);

        $this->assertEquals([
            ['discount' => 'free-shipping', 'description' => 'Free Shipping', 'amount' => 500],
        ], $cart->get('discount_breakdown'));

        $this->assertEquals(0, $cart->shippingTotal());
        $this->assertEquals(500, $cart->discountTotal());
        $this->assertEquals(2500, $cart->grandTotal());
    }

    #[Test]
    public function it_does_nothing_until_a_shipping_option_is_selected()
    {
        Discount::make()->handle('free-shipping')->title('Free Shipping')->type('free_shipping')->save();

        $cart = Cart::make()->lineItems([['id' => 'abc', 'product' => 'product-id', 'quantity' => 1]]);

        $cart = Calculator::calculate($cart);

        $this->assertNull($cart->get('discount_breakdown'));
        $this->assertEquals(0, $cart->shippingTotal());
        $this->assertEquals(0, $cart->discountTotal());
        $this->assertEquals(2500, $cart->grandTotal());
    }

    #[Test]
    #[DataProvider('unmetConditionsProvider')]
    public function it_does_nothing_when_conditions_are_not_met(callable $configureDiscount)
    {
        $configureDiscount(Discount::make()->handle('free-shipping')->title('Free Shipping')->type('free_shipping'))->save();

        $cart = Cart::make()
            ->customer(tap(User::make()->id('jane')->email('jane@example.com'))->save())
            ->lineItems([['id' => 'abc', 'product' => 'product-id', 'quantity' => 1]])
            ->data(['shipping_method' => 'paid_shipping', 'shipping_option' => 'the_only_option']);

        $cart = Calculator::calculate($cart);

        $this->assertNull($cart->get('discount_breakdown'));
        $this->assertEquals(500, $cart->shippingTotal());
        $this->assertEquals(0, $cart->discountTotal());
        $this->assertEquals(3000, $cart->grandTotal());
    }

    #[Test]
    public function it_discounts_shipping_for_an_eligible_customer()
    {
        Discount::make()->handle('free-shipping')->title('Free Shipping')->type('free_shipping')->set('customers', ['jane'])->save();

        $cart = Cart::make()
            ->customer(tap(User::make()->id('jane')->email('jane@example.com'))->save())
            ->lineItems([['id' => 'abc', 'product' => 'product-id', 'quantity' => 1]])
            ->data(['shipping_method' => 'paid_shipping', 'shipping_option' => 'the_only_option']);

        $cart = Calculator::calculate($cart);

        $this->assertEquals([
            ['discount' => 'free-shipping', 'description' => 'Free Shipping', 'amount' => 500],
        ], $cart->get('discount_breakdown'));

        $this->assertEquals(0, $cart->shippingTotal());
        $this->assertEquals(2500, $cart->grandTotal());
    }

    public static function unmetConditionsProvider(): array
    {
        return [
            'start date is in the future' => [fn (DiscountContract $discount) => $discount->set('start_date', now()->addDay()->toDateString())],
            'end date is in the past' => [fn (DiscountContract $discount) => $discount->set('end_date', now()->subDay()->toDateString())],
            'minimum order value is not reached' => [fn (DiscountContract $discount) => $discount->set('minimum_order_value', 5000)],
            'maximum uses have been reached' => [fn (DiscountContract $discount) => $discount->set('maximum_uses', 5)->set('redemptions_count', 5)],
            'customer is not eligible' => [fn (DiscountContract $discount) => $discount->set('customers', ['john'])],
            'cart does not contain an eligible product' => [fn (DiscountContract $discount) => $discount->set('products', ['another-product-id'])],
        ];
    }

    #[Test]
    public function it_discounts_shipping_when_the_cart_contains_an_eligible_product()
    {
        Entry::make()->id('another-product-id')->collection('products')->data(['price' => 1000])->save();

        Discount::make()->handle('free-shipping')->title('Free Shipping')->type('free_shipping')->set('products', ['another-product-id'])->save();

        $cart = Cart::make()
            ->lineItems([
                ['id' => 'abc', 'product' => 'product-id', 'quantity' => 1],
                ['id' => 'def', 'product' => 'another-product-id', 'quantity' => 1],
            ])
            ->data(['shipping_method' => 'paid_shipping', 'shipping_option' => 'the_only_option']);

        $cart = Calculator::calculate($cart);

        $this->assertEquals([
            ['discount' => 'free-shipping', 'description' => 'Free Shipping', 'amount' => 500],
        ], $cart->get('discount_breakdown'));

        $this->assertEquals(0, $cart->shippingTotal());
        $this->assertEquals(3500, $cart->grandTotal());
    }

    #[Test]
    public function it_stacks_with_a_percentage_off_discount_code()
    {
        Discount::make()->handle('free-shipping')->title('Free Shipping')->type('free_shipping')->save();
        Discount::make()->handle('ten-off')->title('Ten Off')->set('discount_code', 'TENOFF')->type('percentage_off')->set('percentage_off', 10)->save();

        $cart = Cart::make()
            ->lineItems([['id' => 'abc', 'product' => 'product-id', 'quantity' => 1]])
            ->data(['discount_code' => 'TENOFF', 'shipping_method' => 'paid_shipping', 'shipping_option' => 'the_only_option']);

        $cart = Calculator::calculate($cart);

        $this->assertEquals([
            ['discount' => 'free-shipping', 'description' => 'Free Shipping', 'amount' => 500],
            ['discount' => 'ten-off', 'description' => 'TENOFF', 'amount' => 250],
        ], $cart->get('discount_breakdown'));

        $this->assertEquals(0, $cart->shippingTotal());
        $this->assertEquals(750, $cart->discountTotal());
        $this->assertEquals(250, $cart->lineItems()->find('abc')->discountTotal());
        $this->assertEquals(2250, $cart->grandTotal());
    }

    #[Test]
    public function it_can_be_redeemed_with_a_discount_code()
    {
        Discount::make()->handle('free-shipping')->title('Free Shipping')->set('discount_code', 'FREESHIP')->type('free_shipping')->save();

        $cart = Cart::make()
            ->lineItems([['id' => 'abc', 'product' => 'product-id', 'quantity' => 1]])
            ->data(['discount_code' => 'FREESHIP', 'shipping_method' => 'paid_shipping', 'shipping_option' => 'the_only_option']);

        $cart = Calculator::calculate($cart);

        $this->assertEquals([
            ['discount' => 'free-shipping', 'description' => 'FREESHIP', 'amount' => 500],
        ], $cart->get('discount_breakdown'));

        $this->assertEquals(0, $cart->shippingTotal());
        $this->assertEquals(2500, $cart->grandTotal());
    }

    #[Test]
    public function it_does_not_charge_tax_on_discounted_shipping()
    {
        config()->set('statamic.cargo.taxes', [
            'price_includes_tax' => false,
            'shipping_tax_behaviour' => 'tax_class',
        ]);

        File::delete($path = base_path('content/cargo/tax-zones.yaml'));
        File::ensureDirectoryExists(Str::beforeLast($path, '/'));

        TaxClass::make()->handle('standard')->set('title', 'Standard')->save();

        TaxZone::make()->handle('uk')->data([
            'title' => 'United Kingdom',
            'type' => 'countries',
            'countries' => ['GBR'],
            'rates' => ['standard' => 20, 'shipping' => 20],
        ])->save();

        Discount::make()->handle('free-shipping')->title('Free Shipping')->type('free_shipping')->save();

        $cart = Cart::make()
            ->lineItems([['id' => 'abc', 'product' => 'product-id', 'quantity' => 1]])
            ->data([
                'shipping_method' => 'paid_shipping',
                'shipping_option' => 'the_only_option',
                'shipping_address' => ['line_1' => '123 Fake St', 'city' => 'Fakeville', 'postcode' => 'FA 1234', 'country' => 'GBR'],
            ]);

        $cart = Calculator::calculate($cart);

        $this->assertEquals(0, $cart->shippingTotal());
        $this->assertEquals(0, $cart->get('shipping_tax_total'));
        $this->assertEquals(500, $cart->taxTotal());
        $this->assertEquals(500, $cart->discountTotal());
        $this->assertEquals(3000, $cart->grandTotal());
    }
}
