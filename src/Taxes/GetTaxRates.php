<?php

namespace DuncanMcClean\Cargo\Taxes;

use DuncanMcClean\Cargo\Contracts\Taxes\TaxClass;
use DuncanMcClean\Cargo\Contracts\Taxes\TaxZone as TaxZoneContract;
use DuncanMcClean\Cargo\Data\Address;
use DuncanMcClean\Cargo\Facades\TaxZone;
use Illuminate\Support\Collection;

class GetTaxRates
{
    public function __invoke(Address $address, TaxClass $taxClass): Collection
    {
        return TaxZone::all()
            ->filter(function (TaxZoneContract $taxZone) use ($address) {
                if ($taxZone->get('type') === 'countries') {
                    return in_array($address->country, $taxZone->get('countries'));
                }

                if ($taxZone->get('type') === 'states') {
                    return in_array($address->country, $taxZone->get('countries'))
                        && in_array($address->state, $taxZone->get('states'));
                }

                if ($taxZone->get('type') === 'postcodes') {
                    $matchesPostcode = collect($taxZone->get('postcodes'))
                        ->filter(fn ($postcode) => fnmatch($postcode, $address->postcode))
                        ->isNotEmpty();

                    return in_array($address->country, $taxZone->get('countries')) && $matchesPostcode;
                }
            })
            ->map(fn (TaxZoneContract $taxZone) => $taxZone->rates()->get($taxClass->handle()))
            ->filter();
    }
}
