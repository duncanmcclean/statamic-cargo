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
}
