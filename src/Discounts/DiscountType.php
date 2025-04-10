<?php

namespace DuncanMcClean\Cargo\Discounts;

enum DiscountType: string
{
    case Fixed = 'fixed';
    case Percentage = 'percentage';

    public static function label($status): string
    {
        return match ($status) {
            self::Fixed => __('Fixed Discount'),
            self::Percentage => __('Percentage Discount'),
        };
    }
}
