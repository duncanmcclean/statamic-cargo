<?php

namespace DuncanMcClean\Cargo\Discounts\Types;

use DuncanMcClean\Cargo\Contracts\Cart\Cart;
use DuncanMcClean\Cargo\Orders\LineItem;

class AmountOff extends DiscountType
{
    protected static $title = 'Amount off';

    public function calculate(Cart $cart, LineItem $lineItem): int
    {
        $discountValue = (int) $this->discount->get('amount_off');

        $eligibleSubtotal = $cart->lineItems()
            ->filter(fn (LineItem $line) => $this->isValidForLineItem($cart, $line))
            ->sum(fn (LineItem $line) => $line->total());

        if ($eligibleSubtotal <= 0) {
            return 0;
        }

        // Don't discount more than the cart subtotal.
        if ($discountValue > $eligibleSubtotal) {
            $discountValue = $eligibleSubtotal;
        }

        // Calculate this line item's proportional share of the discount.
        $lineTotal = $lineItem->total();
        $proportion = $lineTotal / $eligibleSubtotal;

        return (int) floor($discountValue * $proportion);
    }

    public function fieldItems(): array
    {
        return [
            'amount_off' => [
                'display' => __('Amount'),
                'type' => 'money',
                'validate' => ['required', 'min:0'],
            ],
        ];
    }
}
