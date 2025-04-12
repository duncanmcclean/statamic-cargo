<?php

namespace DuncanMcClean\Cargo\Discounts\Types;

use DuncanMcClean\Cargo\Contracts\Cart\Cart;
use DuncanMcClean\Cargo\Orders\LineItem;

class AmountOff extends DiscountType
{
    protected static $title = 'Amount off';

    public function calculate(Cart $cart, LineItem $lineItem): int
    {
        return (int) $this->discount->get('amount_off');
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
