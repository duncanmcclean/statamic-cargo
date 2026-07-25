<?php

namespace DuncanMcClean\Cargo\Cart\Calculator;

use Closure;
use DuncanMcClean\Cargo\Cart\Cart;
use DuncanMcClean\Cargo\Contracts\Taxes\Driver as TaxDriver;
use DuncanMcClean\Cargo\Data\Address;
use DuncanMcClean\Cargo\Orders\LineItem;

class CalculateTaxes
{
    public function handle(Cart $cart, Closure $next)
    {
        $taxableAddress = $cart->taxableAddress() ?? $this->defaultAddress();

        if (! $taxableAddress) {
            return $next($cart);
        }

        $taxBreakdowns = collect();

        $cart->lineItems()->each(function (LineItem $lineItem) use ($taxableAddress, &$taxBreakdowns) {
            $lineItemTotal = $lineItem->total();

            if ($lineItem->discountTotal()) {
                $lineItemTotal -= $lineItem->discountTotal();
            }

            $taxBreakdown = app(TaxDriver::class)
                ->setAddress($taxableAddress)
                ->setPurchasable($lineItem->variant() ?? $lineItem->product())
                ->setLineItem($lineItem)
                ->getBreakdown($lineItemTotal);

            $taxBreakdowns = $taxBreakdowns->merge($taxBreakdown);

            if ($taxBreakdown->isEmpty()) {
                return;
            }

            $lineItem->set('tax_breakdown', $taxBreakdown->toArray());
            $lineItem->taxTotal((int) $taxBreakdown->sum('amount'));

            if (config('statamic.cargo.taxes.price_includes_tax')) {
                $lineItem->total($lineItemTotal);
            } else {
                $lineItem->total($lineItemTotal + $lineItem->taxTotal());
            }
        });

        $shippingOption = $cart->shippingOption();

        if ($shippingOption) {
            $shippingTotal = $cart->shippingTotal();

            $shippingTaxBreakdown = app(TaxDriver::class)
                ->setAddress($taxableAddress)
                ->setPurchasable($shippingOption)
                ->getBreakdown($shippingTotal);

            $taxBreakdowns = $taxBreakdowns->merge($shippingTaxBreakdown);

            $cart->set('shipping_tax_breakdown', $shippingTaxBreakdown->toArray());
            $cart->set('shipping_tax_total', $shippingTaxTotal = (int) $shippingTaxBreakdown->sum('amount'));

            if (config('statamic.cargo.taxes.price_includes_tax')) {
                $cart->shippingTotal($shippingTotal);
            } else {
                $cart->shippingTotal($shippingTotal + $shippingTaxTotal);
            }
        }

        $cart->taxTotal($taxBreakdowns->sum('amount'));

        return $next($cart);
    }

    private function defaultAddress(): ?Address
    {
        $address = array_filter(config('statamic.cargo.taxes.default_address') ?? []);

        if (empty($address)) {
            return null;
        }

        return Address::make($address);
    }
}
