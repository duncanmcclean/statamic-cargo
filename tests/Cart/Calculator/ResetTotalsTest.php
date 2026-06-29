<?php

namespace Tests\Cart\Calculator;

use DuncanMcClean\Cargo\Cart\Calculator\ResetTotals;
use DuncanMcClean\Cargo\Facades\Cart;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Collection;
use Statamic\Facades\Entry;
use Statamic\Testing\Concerns\PreventsSavingStacheItemsToDisk;
use Tests\TestCase;

class ResetTotalsTest extends TestCase
{
    use PreventsSavingStacheItemsToDisk;

    protected function setUp(): void
    {
        parent::setUp();

        Collection::make('products')->save();
        Entry::make()->id('product-id')->collection('products')->data(['price' => 500])->save();
    }

    #[Test]
    public function it_resets_cart_totals_to_zero()
    {
        $cart = Cart::make()
            ->grandTotal(1500)
            ->subTotal(1000)
            ->taxTotal(200)
            ->shippingTotal(300)
            ->discountTotal(500);

        $cart = app(ResetTotals::class)->handle($cart, fn ($cart) => $cart);

        $this->assertEquals(0, $cart->grandTotal());
        $this->assertEquals(0, $cart->subTotal());
        $this->assertEquals(0, $cart->taxTotal());
        $this->assertEquals(0, $cart->shippingTotal());
        $this->assertEquals(0, $cart->discountTotal());
    }

    #[Test]
    public function it_clears_a_stale_shipping_tax_total()
    {
        $cart = Cart::make()->set('shipping_tax_total', 60);

        $cart = app(ResetTotals::class)->handle($cart, fn ($cart) => $cart);

        $this->assertFalse($cart->has('shipping_tax_total'));
        $this->assertNull($cart->get('shipping_tax_total'));
    }

    #[Test]
    public function it_clears_a_stale_shipping_tax_breakdown()
    {
        $cart = Cart::make()->set('shipping_tax_breakdown', [
            ['rate' => 20, 'description' => 'VAT', 'zone' => 'uk', 'amount' => 60],
        ]);

        $cart = app(ResetTotals::class)->handle($cart, fn ($cart) => $cart);

        $this->assertFalse($cart->has('shipping_tax_breakdown'));
        $this->assertNull($cart->get('shipping_tax_breakdown'));
    }

    #[Test]
    public function it_clears_a_stale_discount_breakdown()
    {
        $cart = Cart::make()->set('discount_breakdown', [
            ['discount' => 'bulk', 'description' => 'Bulk Quantity Discount', 'amount' => 500],
        ]);

        $cart = app(ResetTotals::class)->handle($cart, fn ($cart) => $cart);

        $this->assertFalse($cart->has('discount_breakdown'));
        $this->assertNull($cart->get('discount_breakdown'));
    }

    #[Test]
    public function it_resets_line_item_totals_to_zero()
    {
        $cart = Cart::make()->lineItems([
            [
                'product' => 'product-id',
                'quantity' => 2,
                'unit_price' => 500,
                'sub_total' => 1000,
                'tax_total' => 200,
                'discount_total' => 100,
                'total' => 1100,
            ],
        ]);

        $cart = app(ResetTotals::class)->handle($cart, fn ($cart) => $cart);

        $lineItem = $cart->lineItems()->first();

        $this->assertEquals(0, $lineItem->unitPrice());
        $this->assertEquals(0, $lineItem->subTotal());
        $this->assertEquals(0, $lineItem->taxTotal());
        $this->assertEquals(0, $lineItem->discountTotal());
        $this->assertEquals(0, $lineItem->total());
    }

    #[Test]
    public function it_clears_a_stale_tax_breakdown_from_line_items()
    {
        $cart = Cart::make()->lineItems([
            [
                'product' => 'product-id',
                'quantity' => 1,
                'unit_price' => 500,
                'tax_breakdown' => [
                    ['rate' => 20, 'description' => 'VAT', 'zone' => 'uk', 'amount' => 100],
                ],
            ],
        ]);

        $cart = app(ResetTotals::class)->handle($cart, fn ($cart) => $cart);

        $lineItem = $cart->lineItems()->first();

        $this->assertFalse($lineItem->has('tax_breakdown'));
        $this->assertNull($lineItem->get('tax_breakdown'));
    }
}
