<?php

namespace Tests\Discounts\Types;

use DuncanMcClean\Cargo\Discounts\Types\AmountOff;
use DuncanMcClean\Cargo\Facades\Cart;
use DuncanMcClean\Cargo\Facades\Discount;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AmountOffTest extends TestCase
{
    #[Test]
    public function it_calculates_amount_off_discount()
    {
        $discount = Discount::make()->type('amount_off')->set('amount_off', 299);

        $cart = Cart::make();
        $cart->lineItems()->create(['id' => 'abc', 'total' => 2599]);

        $lineItem = $cart->lineItems()->find('abc');

        $amount = (new AmountOff)->setDiscount($discount)->calculate($cart, $lineItem);

        $this->assertEquals(299, $amount);
    }

    #[Test]
    public function it_distributes_fixed_amount_across_multiple_lines()
    {
        $discount = Discount::make()->type('amount_off')->set('amount_off', 100);

        $cart = Cart::make();
        $cart->lineItems()->create(['id' => 'line1', 'total' => 1000]);
        $cart->lineItems()->create(['id' => 'line2', 'total' => 1000]);

        $discountType = (new AmountOff)->setDiscount($discount);

        $amountLine1 = $discountType->calculate($cart, $cart->lineItems()->find('line1'));
        $amountLine2 = $discountType->calculate($cart, $cart->lineItems()->find('line2'));

        // 100 rappen distributed across 2 equal lines = 50 each
        $this->assertEquals(50, $amountLine1);
        $this->assertEquals(50, $amountLine2);
        $this->assertEquals(100, $amountLine1 + $amountLine2);
    }

    #[Test]
    public function it_distributes_fixed_amount_proportionally_by_line_item_value()
    {
        $discount = Discount::make()->type('amount_off')->set('amount_off', 100);

        $cart = Cart::make();
        $cart->lineItems()->create(['id' => 'line1', 'total' => 1000]); // 25%
        $cart->lineItems()->create(['id' => 'line2', 'total' => 3000]); // 75%

        $discountType = (new AmountOff)->setDiscount($discount);

        $amountLine1 = $discountType->calculate($cart, $cart->lineItems()->find('line1'));
        $amountLine2 = $discountType->calculate($cart, $cart->lineItems()->find('line2'));

        // 100 rappen distributed 25/75 = 25 and 75
        $this->assertEquals(25, $amountLine1);
        $this->assertEquals(75, $amountLine2);
        $this->assertEquals(100, $amountLine1 + $amountLine2);
    }
}
