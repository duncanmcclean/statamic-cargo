<?php

namespace DuncanMcClean\Cargo\Cart\Calculator;

use Closure;
use DuncanMcClean\Cargo\Cart\Cart;
use DuncanMcClean\Cargo\Contracts\Taxes\Driver as TaxDriver;
use DuncanMcClean\Cargo\Data\Address;
use DuncanMcClean\Cargo\Orders\LineItem;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

class CalculateTaxes
{
    public function handle(Cart $cart, Closure $next)
    {
        $taxableAddress = $cart->taxableAddress() ?? $this->defaultAddress($cart);

        if (! $taxableAddress) {
            return $next($cart);
        }

        $taxBreakdowns = app(TaxDriver::class)->calculate($cart, $taxableAddress);

        $cart->lineItems()->each(function (LineItem $lineItem) use ($taxBreakdowns) {
            $this->applyToLineItem($lineItem, $taxBreakdowns->get($lineItem->id(), collect()));
        });

        if ($cart->shippingOption()) {
            $this->applyToShipping($cart, $taxBreakdowns->get('shipping', collect()));
        }

        $cart->taxTotal($taxBreakdowns->sum(fn (Collection $taxBreakdown) => $taxBreakdown->sum('amount')));

        return $next($cart);
    }

    private function applyToLineItem(LineItem $lineItem, Collection $taxBreakdown): void
    {
        if ($taxBreakdown->isEmpty()) {
            return;
        }

        $lineItemTotal = $lineItem->total() - $lineItem->discountTotal();

        $lineItem->set('tax_breakdown', $taxBreakdown->toArray());
        $lineItem->taxTotal((int) $taxBreakdown->sum('amount'));

        if (config('statamic.cargo.taxes.price_includes_tax')) {
            $lineItem->total($lineItemTotal);
        } else {
            $lineItem->total($lineItemTotal + $lineItem->taxTotal());
        }
    }

    private function applyToShipping(Cart $cart, Collection $taxBreakdown): void
    {
        $shippingTotal = $cart->shippingTotal();

        $cart->set('shipping_tax_breakdown', $taxBreakdown->toArray());
        $cart->set('shipping_tax_total', $shippingTaxTotal = (int) $taxBreakdown->sum('amount'));

        if (config('statamic.cargo.taxes.price_includes_tax')) {
            $cart->shippingTotal($shippingTotal);
        } else {
            $cart->shippingTotal($shippingTotal + $shippingTaxTotal);
        }
    }

    private function defaultAddress(Cart $cart): ?Address
    {
        $defaultAddress = config('statamic.cargo.taxes.default_address') ?? [];

        if (isset($defaultAddress[$cart->site()->handle()])) {
            $defaultAddress = $defaultAddress[$cart->site()->handle()];
        }

        $address = array_filter(Arr::only($defaultAddress, ['country', 'state', 'postcode']));

        if (empty($address)) {
            return null;
        }

        return Address::make($address);
    }
}
