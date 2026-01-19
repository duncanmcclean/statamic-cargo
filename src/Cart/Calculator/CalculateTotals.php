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

        // Apply any discounts to the total before adding shipping.
        $total = $total - $cart->discountTotal();

        // Add shipping costs to the total
        $total += $cart->shippingTotal();

        $cart->grandTotal($total);

        return $next($cart);
    }
}
