<?php

namespace DuncanMcClean\Cargo\Taxes;

use DuncanMcClean\Cargo\Contracts\Purchasable;
use DuncanMcClean\Cargo\Contracts\Taxes\Driver as DriverContract;
use DuncanMcClean\Cargo\Data\Address;
use DuncanMcClean\Cargo\Facades\TaxZone as TaxZoneFacade;
use DuncanMcClean\Cargo\Orders\LineItem;
use Illuminate\Support\Collection;

class DefaultTaxDriver implements DriverContract
{
    public $address;
    public $purchasable;
    public $lineItem;

    public function setAddress(Address $address): self
    {
        $this->address = $address;

        return $this;
    }

    public function setPurchasable(Purchasable $purchasable): self
    {
        $this->purchasable = $purchasable;

        return $this;
    }

    public function setLineItem(LineItem $lineItem): self
    {
        $this->lineItem = $lineItem;

        return $this;
    }

    public function getBreakdown(int $total): Collection
    {
        if (! $this->purchasable->purchasableTaxClass()) {
            return collect();
        }

        $breakdown = collect();
        $taxRates = (new GetTaxRates)($this->address, $this->taxClass());

        if (config('statamic.cargo.taxes.price_includes_tax')) {
            $totalTaxPercentage = $taxRates->sum() / 100; // E.g. 0.2 for 20%
            $priceExcludingTax = round($total / (1 + $totalTaxPercentage));

            foreach ($taxRates as $taxZone => $taxRate) {
                $taxAmount = (int) round($priceExcludingTax * ($taxRate / 100));

                $taxZone = TaxZoneFacade::find($taxZone);

                $breakdown->push(TaxCalculation::make(
                    rate: $taxRate,
                    description: $this->taxClass()->get('name'),
                    zone: $taxZone->get('name'),
                    amount: $taxAmount
                ));
            }

            return $breakdown;
        }

        foreach ($taxRates as $taxZone => $taxRate) {
            $taxAmount = (int) round($total * ($taxRate / 100));

            $taxZone = TaxZoneFacade::find($taxZone);

            $breakdown->push(TaxCalculation::make(
                rate: $taxRate,
                description: $this->taxClass()->get('name'),
                zone: $taxZone->get('name'),
                amount: $taxAmount
            ));
        }

        return $breakdown;
    }

    private function taxClass()
    {
        return $this->purchasable->purchasableTaxClass();
    }
}
