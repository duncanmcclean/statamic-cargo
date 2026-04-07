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

        $eligibleLineItems = $cart->lineItems()->filter(fn (LineItem $line) => $this->isValidForLineItem($cart, $line));
        $eligibleSubtotal = $eligibleLineItems->sum(fn (LineItem $line) => $line->total());

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

        // If this is the last eligible line item, give it the remainder to avoid
        // losing cents due to rounding errors when the discount isn't perfectly divisible.
        $lastEligibleLineItem = $eligibleLineItems->last();

        if ($lastEligibleLineItem && $lastEligibleLineItem->id() === $lineItem->id()) {
            $allocatedToOthers = $eligibleLineItems
                ->filter(fn (LineItem $line) => $line->id() !== $lineItem->id())
                ->sum(fn (LineItem $line) => (int) floor($discountValue * ($line->total() / $eligibleSubtotal)));

            return $discountValue - $allocatedToOthers;
        }

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
