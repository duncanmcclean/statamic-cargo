<?php

namespace DuncanMcClean\Cargo\Cart\Calculator;

use Closure;
use DuncanMcClean\Cargo\Cart\Cart;

class CalculateTotals
{
    public function handle(Cart $cart, Closure $next)
    {
        $pricesIncludeTax = config('statamic.cargo.taxes.price_includes_tax');

        // Calculate the total (subtotal + taxes if they aren't included in the prices)
        $total = $cart->subTotal();

        if (! $pricesIncludeTax) {
            $total += $cart->lineItems()->map->taxTotal()->sum();
        }

        // Apply any line item discounts to the total before adding shipping.
        // Shipping discounts are already reflected in the shipping total,
        // so we don't need to do anything about them here.
        $total = $total - $cart->lineItems()->map->discountTotal()->sum();

        // Add shipping costs to the total
        $total += $cart->shippingTotal();

        $cart->grandTotal($total);

        return $next($cart);
    }
}
