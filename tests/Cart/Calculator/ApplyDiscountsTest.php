<?php

namespace Tests\Cart\Calculator;

use DuncanMcClean\Cargo\Cart\Calculator\ApplyDiscounts;
use DuncanMcClean\Cargo\Facades\Cart;
use DuncanMcClean\Cargo\Facades\Discount;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Collection;
use Statamic\Facades\Entry;
use Statamic\Testing\Concerns\PreventsSavingStacheItemsToDisk;
use Tests\TestCase;

class ApplyDiscountsTest extends TestCase
{
    use PreventsSavingStacheItemsToDisk;

    #[Test]
    public function automatically_applies_discounts_without_codes()
    {
        $this->makeProduct('123')->set('price', 2500)->save();
        $this->makeProduct('456')->set('price', 5000)->save();

        $discountA = Discount::make()->id('a')->name('Discount A')->set('discount_code', 'A')->type('percentage_off')->set('percentage_off', 10); // Shouldn't be applied, it has a coupon code.
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

        $cart = app(ApplyDiscounts::class)->handle($cart, fn ($cart) => $cart);

        $this->assertEquals([
            ['discount' => 'b', 'description' => 'Discount B', 'amount' => 375],
            ['discount' => 'c', 'description' => 'Discount C', 'amount' => 100],
        ], $cart->lineItems()->find('abc')->get('discounts'));
        $this->assertEquals(475, $cart->lineItems()->find('abc')->discountTotal());

        $this->assertEquals([
            ['discount' => 'b', 'description' => 'Discount B', 'amount' => 750],
        ], $cart->lineItems()->find('def')->get('discounts'));
        $this->assertEquals(750, $cart->lineItems()->find('def')->discountTotal());

        $this->assertEquals(1225, $cart->discountTotal());
    }

    #[Test]
    public function applies_discount_code()
    {
        $this->makeProduct('123')->set('price', 2500)->save();
        $this->makeProduct('456')->set('price', 5000)->save();

        $discountA = Discount::make()->id('a')->name('Discount A')->set('discount_code', 'A')->type('percentage_off')->set('percentage_off', 10)->set('products', ['123']); // Should only be applied to the first line item.
        $discountB = Discount::make()->id('b')->name('Discount B')->type('percentage_off')->set('percentage_off', 15); // Site-wide discount, should be applied to both line items.

        $discountA->save();
        $discountB->save();

        $cart = Cart::make()->set('discount_code', 'A')->lineItems([
            ['id' => 'abc', 'product' => '123', 'quantity' => 1, 'total' => 2500],
            ['id' => 'def', 'product' => '456', 'quantity' => 1, 'total' => 5000],
        ]);

        $cart = app(ApplyDiscounts::class)->handle($cart, fn ($cart) => $cart);

        $this->assertEquals([
            ['discount' => 'b', 'description' => 'Discount B', 'amount' => 375],
            ['discount' => 'a', 'description' => 'A', 'amount' => 250],
        ], $cart->lineItems()->find('abc')->get('discounts'));
        $this->assertEquals(625, $cart->lineItems()->find('abc')->discountTotal());

        $this->assertEquals([
            ['discount' => 'b', 'description' => 'Discount B', 'amount' => 750],
        ], $cart->lineItems()->find('def')->get('discounts'));
        $this->assertEquals(750, $cart->lineItems()->find('def')->discountTotal());

        $this->assertEquals(1375, $cart->discountTotal());
    }

    #[Test]
    public function discount_code_is_removed_from_cart_if_it_does_not_exist()
    {
        $this->makeProduct('123')->set('price', 2500)->save();

        $cart = Cart::make()->set('discount_code', 'NON_EXISTENT')->lineItems([
            ['id' => 'abc', 'product' => '123', 'quantity' => 1, 'total' => 2500],
        ]);

        $cart = app(ApplyDiscounts::class)->handle($cart, fn ($cart) => $cart);

        $this->assertNull($cart->lineItems()->find('abc')->get('discounts'));
        $this->assertEquals(0, $cart->lineItems()->find('abc')->discountTotal());

        $this->assertFalse($cart->has('discount_code'));
        $this->assertEquals(0, $cart->discountTotal());
    }

    #[Test]
    public function ensures_discount_total_does_not_exceed_line_item_total()
    {
        $this->makeProduct('123')->set('price', 2500)->save();

        $discountA = Discount::make()->id('a')->name('Discount A')->type('percentage_off')->set('percentage_off', 50);
        $discountB = Discount::make()->id('b')->name('Discount B')->type('amount_off')->set('amount_off', 1300)->set('products', ['123']);

        $discountA->save();
        $discountB->save();

        $cart = Cart::make()->lineItems([
            ['id' => 'abc', 'product' => '123', 'quantity' => 1, 'total' => 2500],
        ]);

        $cart = app(ApplyDiscounts::class)->handle($cart, fn ($cart) => $cart);

        $this->assertEquals([
            ['discount' => 'a', 'description' => 'Discount A', 'amount' => 1250],
            ['discount' => 'b', 'description' => 'Discount B', 'amount' => 1300],
        ], $cart->lineItems()->find('abc')->get('discounts'));

        // Should be capped at the line item total.
        $this->assertEquals(2500, $cart->lineItems()->find('abc')->discountTotal());

        $this->assertEquals(2500, $cart->discountTotal());
    }

    protected function makeProduct($id = null)
    {
        Collection::make('products')->save();

        return tap(Entry::make()->collection('products')->id($id))->save();
    }
}
