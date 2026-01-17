<?php

namespace Tests\Discounts\Types;

use DuncanMcClean\Cargo\Discounts\Types\PercentageOff;
use DuncanMcClean\Cargo\Facades\Cart;
use DuncanMcClean\Cargo\Facades\Discount;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class PercentageOffTest extends TestCase
{
    #[Test]
    public function it_calculates_percentage_off_discount()
    {
        $discount = Discount::make()->type('percentage_off')->set('percentage_off', 10);

        $cart = Cart::make();
        $cart->lineItems()->create(['id' => 'abc', 'total' => 2599]);

        $lineItem = $cart->lineItems()->find('abc');

        $amount = (new PercentageOff)->setDiscount($discount)->calculate($cart, $lineItem);

        $this->assertEquals(259, $amount);
    }

    #[Test]
    public function it_applies_percentage_to_each_line_independently()
    {
        $discount = Discount::make()->type('percentage_off')->set('percentage_off', 10);

        $cart = Cart::make();
        $cart->lineItems()->create(['id' => 'line1', 'total' => 1000]);
        $cart->lineItems()->create(['id' => 'line2', 'total' => 2000]);

        $discountType = (new PercentageOff)->setDiscount($discount);

        $amountLine1 = $discountType->calculate($cart, $cart->lineItems()->find('line1'));
        $amountLine2 = $discountType->calculate($cart, $cart->lineItems()->find('line2'));

        // 10% of each line
        $this->assertEquals(100, $amountLine1);
        $this->assertEquals(200, $amountLine2);

        // Total discount should equal 10% of cart subtotal (3000)
        $this->assertEquals(300, $amountLine1 + $amountLine2);
    }
}
