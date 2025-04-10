<?php

namespace Tests\Cart\Calculator;

use DuncanMcClean\Cargo\Cart\Calculator\ApplyDiscounts;
use DuncanMcClean\Cargo\Discounts\DiscountType;
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

    protected function setUp(): void
    {
        parent::setUp();

        // We shouldn't really need to do this. It's come up in another test too.
        // Need to investigate.
        Discount::all()->each->delete();
    }

    #[Test]
    public function automatically_applies_discounts_without_codes()
    {
        $this->makeProduct('123')->set('price', 2500)->save();
        $this->makeProduct('456')->set('price', 5000)->save();

        $discountA = Discount::make()->id('a')->name('Discount A')->code('A')->type(DiscountType::Percentage)->amount(10); // Shouldn't be applied, it has a coupon code.
        $discountB = Discount::make()->id('b')->name('Discount B')->type(DiscountType::Percentage)->amount(15); // Should be applied to both line items.
        $discountC = Discount::make()->id('c')->name('Discount C')->type(DiscountType::Fixed)->amount(100)->set('products', ['123']); // Should be applied to the first line item.
        $discountD = Discount::make()->id('d')->name('Discount D')->type(DiscountType::Fixed)->amount(200)->set('products', ['456'])->set('expires_at', '2025-01-01'); // Shouldn't be applied, it has expired.

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
            ['discount' => 'b', 'type' => 'percentage', 'amount' => 375],
            ['discount' => 'c', 'type' => 'fixed', 'amount' => 100],
        ], $cart->lineItems()->find('abc')->get('discounts'));
        $this->assertEquals(475, $cart->lineItems()->find('abc')->discountTotal());

        $this->assertEquals([
            ['discount' => 'b', 'type' => 'percentage', 'amount' => 750],
        ], $cart->lineItems()->find('def')->get('discounts'));
        $this->assertEquals(750, $cart->lineItems()->find('def')->discountTotal());

        $this->assertEquals(1225, $cart->discountTotal());
    }

    #[Test]
    public function applies_discount_code()
    {
        $this->makeProduct('123')->set('price', 2500)->save();
        $this->makeProduct('456')->set('price', 5000)->save();

        $discountA = Discount::make()->id('a')->name('Discount A')->code('A')->type(DiscountType::Percentage)->amount(10)->set('products', ['123']); // Should only be applied to the first line item.
        $discountB = Discount::make()->id('b')->name('Discount B')->type(DiscountType::Percentage)->amount(15); // Site-wide discount, should be applied to both line items.

        $discountA->save();
        $discountB->save();

        $cart = Cart::make()->set('discount_code', 'A')->lineItems([
            ['id' => 'abc', 'product' => '123', 'quantity' => 1, 'total' => 2500],
            ['id' => 'def', 'product' => '456', 'quantity' => 1, 'total' => 5000],
        ]);

        $cart = app(ApplyDiscounts::class)->handle($cart, fn ($cart) => $cart);

        $this->assertEquals([
            ['discount' => 'b', 'type' => 'percentage', 'amount' => 375],
            ['discount' => 'a', 'type' => 'percentage', 'amount' => 250],
        ], $cart->lineItems()->find('abc')->get('discounts'));
        $this->assertEquals(625, $cart->lineItems()->find('abc')->discountTotal());

        $this->assertEquals([
            ['discount' => 'b', 'type' => 'percentage', 'amount' => 750],
        ], $cart->lineItems()->find('def')->get('discounts'));
        $this->assertEquals(750, $cart->lineItems()->find('def')->discountTotal());

        $this->assertEquals(1375, $cart->discountTotal());
    }



    #[Test]
    public function applies_percentage_discount()
    {
        $this->markTestIncomplete('Extract out once we make discount types flexible.');

        $this->makeProduct('123')->set('price', 2500)->save();

        $coupon = tap(Discount::make()->code('foobar')->type(DiscountType::Percentage)->amount(50))->save();

        $cart = Cart::make()->coupon($coupon->id())->lineItems([
            ['id' => 'abc', 'product' => '123', 'quantity' => 1, 'total' => 2500],
        ]);

        $cart = app(ApplyDiscounts::class)->handle($cart, fn ($cart) => $cart);

        $this->assertEquals($cart->discountTotal(), 1250);
        $this->assertEquals($cart->lineItems()->find('abc')->get('discount_amount'), 1250);
    }

    #[Test]
    public function applies_fixed_discount()
    {
        $this->markTestIncomplete('Extract out once we make discount types flexible.');

        $this->makeProduct('123')->set('price', 2500)->save();

        $coupon = tap(Discount::make()->code('foobar')->type(DiscountType::Fixed)->amount(450))->save();

        $cart = Cart::make()->coupon($coupon->id())->lineItems([
            ['id' => 'abc', 'product' => '123', 'quantity' => 1, 'total' => 2500],
        ]);

        $cart = app(ApplyDiscounts::class)->handle($cart, fn ($cart) => $cart);

        $this->assertEquals($cart->discountTotal(), 450);
        $this->assertEquals($cart->lineItems()->find('abc')->get('discount_amount'), 450);
    }

    #[Test]
    public function discount_is_removed_from_line_items_when_no_longer_eligible()
    {
        $this->markTestSkipped('Skipping, needs re-doing after discount codes refactor.');

        $this->makeProduct('123')->set('price', 2500)->save();
        $this->makeProduct('456')->set('price', 5000)->save();

        $coupon = tap(Discount::make()->code('foobar')->type(DiscountType::Percentage)->amount(50)->set('products', ['123', '789']))->save();

        $cart = Cart::make()->coupon($coupon->id())->lineItems([
            ['id' => 'abc', 'product' => '123', 'quantity' => 1, 'total' => 2500, 'discount_amount' => 1250],
            ['id' => 'def', 'product' => '456', 'quantity' => 1, 'total' => 5000, 'discount_amount' => 2500], // This product is no longer eligible.
        ]);

        $cart = app(ApplyDiscounts::class)->handle($cart, fn ($cart) => $cart);

        $this->assertEquals($cart->discountTotal(), 1250);
        $this->assertEquals($cart->lineItems()->find('abc')->get('discount_amount'), 1250);
        $this->assertNull($cart->lineItems()->find('def')->get('discount_amount'));
    }

    protected function makeProduct($id = null)
    {
        Collection::make('products')->save();

        return tap(Entry::make()->collection('products')->id($id))->save();
    }
}
