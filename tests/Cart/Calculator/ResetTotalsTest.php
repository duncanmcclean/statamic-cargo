<?php

namespace Tests\Cart\Calculator;

use DuncanMcClean\Cargo\Cart\Calculator\ResetTotals;
use DuncanMcClean\Cargo\Facades\Cart;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Testing\Concerns\PreventsSavingStacheItemsToDisk;
use Tests\TestCase;

class ResetTotalsTest extends TestCase
{
    use PreventsSavingStacheItemsToDisk;

    #[Test]
    public function it_clears_a_stale_discount_breakdown()
    {
        // A discount_breakdown left over from a previous calculation (for example a
        // bulk-quantity discount that applied before the quantity dropped back below
        // the tier) must not survive a recalculation, otherwise the cart keeps
        // showing a phantom discount line even though discountTotal is reset to zero.
        $cart = Cart::make()->set('discount_breakdown', [
            ['discount' => 'bulk', 'description' => 'Bulk Quantity Discount', 'amount' => 500],
        ]);

        $cart = app(ResetTotals::class)->handle($cart, fn ($cart) => $cart);

        $this->assertFalse($cart->has('discount_breakdown'));
        $this->assertNull($cart->get('discount_breakdown'));
    }
}
