<?php

namespace DuncanMcClean\Cargo\Rules;

use Closure;
use DuncanMcClean\Cargo\Facades\TaxZone;
use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Contracts\Validation\ValidationRule;

class UniqueTaxZone implements DataAwareRule, ValidationRule
{
    /**
     * All of the data under validation.
     *
     * @var array<string, mixed>
     */
    protected $data = [];

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($value === 'countries') {
            $countries = $this->data['countries'];

            $overlapsWithAnotherTaxZone = TaxZone::all()
                ->filter(fn ($taxZone) => $taxZone->get('type') === 'countries')
                ->when(
                    request()->route('tax_zone'),
                    fn ($taxZones) => $taxZones->reject(fn ($taxZone) => $taxZone->handle() === request()->route('tax_zone')->handle())
                )
                ->filter(function ($taxZone) use ($countries) {
                    return count(array_intersect($taxZone->get('countries'), $countries)) === count($countries);
                })
                ->isNotEmpty();

            if ($overlapsWithAnotherTaxZone) {
                $fail(__('cargo::validation.unique_tax_zone.countries'));
            }
        }

        if ($value === 'states') {
            $states = $this->data['states'];

            $overlapsWithAnotherTaxZone = TaxZone::all()
                ->filter(fn ($taxZone) => $taxZone->get('type') === 'states')
                ->when(
                    request()->route('tax_zone'),
                    fn ($taxZones) => $taxZones->reject(fn ($taxZone) => $taxZone->handle() === request()->route('tax_zone')->handle())
                )
                ->filter(function ($taxZone) use ($states) {
                    return count(array_intersect($taxZone->get('states'), $states)) === count($states);
                })
                ->isNotEmpty();

            if ($overlapsWithAnotherTaxZone) {
                $fail(__('cargo::validation.unique_tax_zone.states'));
            }
        }

        if ($value === 'postcodes') {
            $postcodes = $this->data['postcodes'];

            $overlapsWithAnotherTaxZone = TaxZone::all()
                ->filter(fn ($taxZone) => $taxZone->get('type') === 'postcodes')
                ->when(
                    request()->route('tax_zone'),
                    fn ($taxZones) => $taxZones->reject(fn ($taxZone) => $taxZone->handle() === request()->route('tax_zone')->handle())
                )
                ->filter(function ($taxZone) use ($postcodes) {
                    return count(array_intersect($taxZone->get('postcodes'), $postcodes)) === count($postcodes);
                })
                ->isNotEmpty();

            if ($overlapsWithAnotherTaxZone) {
                $fail(__('cargo::validation.unique_tax_zone.postcodes'));
            }
        }
    }

    /**
     * Set the data under validation.
     *
     * @param  array<string, mixed>  $data
     */
    public function setData(array $data): static
    {
        $this->data = $data;

        return $this;
    }
}
