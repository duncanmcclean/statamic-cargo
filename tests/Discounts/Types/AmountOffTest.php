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
}
