<?php

namespace DuncanMcClean\Cargo\Discounts\Types;

use DuncanMcClean\Cargo\Contracts\Cart\Cart;
use DuncanMcClean\Cargo\Orders\LineItem;

class PercentageOff extends DiscountType
{
    protected static $title = 'Percentage off';

    public function calculate(Cart $cart, LineItem $lineItem): int
    {
        $percentage = (int) $this->discount->get('percentage_off');

        return (int) ($percentage * $lineItem->total()) / 100;
    }

    public function fieldItems(): array
    {
        return [
            'percentage_off' => [
                'display' => __('Percentage'),
                'append' => '%',
                'type' => 'integer',
                'validate' => ['required', 'min:1', 'max:100'],
            ],
        ];
    }
}
